<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version        8
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */

namespace con4gis\LdapBundle\Classes;

use con4gis\LdapBundle\Entity\Con4gisLdapFrontendGroups;
use con4gis\LdapBundle\Entity\Con4gisLdapSettings;
use con4gis\LdapBundle\Resources\contao\models\LdapMemberModel;
use Contao\Module;
use con4gis\LdapBundle\Classes\LdapConnection;
use Contao\System;
use Psr\Log\LogLevel;
use Contao\CoreBundle\Monolog\ContaoContext;

class CreateLdapAccount
{
    public function onAccountCreation(int $userId, array $userData, Module $module) {
        
        if ($module && $userData && $userId) {
            $ldapRegistration = $module->c4gLdapRegistration;
            if ($ldapRegistration == "1") {
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
                $ldapSettings = $ldapSettingsRepo->findAll();
                $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();

                if (empty($ldapSettings)) {
                    \System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim Finden der allgemeinen LDAP Einstellungen.', array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                            )));
                    return false;
                }

                $serverType = $ldapSettings[0]->getServerType();
                $mailField = strtolower($ldapSettings[0]->getEmail());
                $firstnameField = strtolower($ldapSettings[0]->getFirstname());
                $lastnameField = strtolower($ldapSettings[0]->getLastname());

                if ($serverType == "windows_ad") {
                    $userRDNKey = "cn";
                    $userRDNObject = $userData['firstname']." ".$userData['lastname'];
                } else {
                    $userRDNKey = "uid";
                    $userRDNObject = $userData['username'];
                }

                $userPwdPlain = $_POST['password'];
                $userPwdHash = base64_encode(hash("sha512", $userPwdPlain, true));
                $ldapUserPwd = "{SHA512}".$userPwdHash;

                //create array for new ldap entry
                //ToDo: check if username is not null
                $adduserAD["cn"][0] = $userData['username'];
                $adduserAD["uid"][0] = $userData['username'];
                $adduserAD["objectclass"][0] = "inetOrgPerson";
                $adduserAD["objectclass"][1] = "person";
                $adduserAD["objectclass"][2] = "organizationalPerson";
                $adduserAD["objectclass"][3] = "top";
                $adduserAD[$firstnameField][0] = $userData['firstname'] ? $userData['firstname'] : "";
                $adduserAD[$lastnameField][0] = $userData['lastname'] ? $userData['lastname'] : "";
                $adduserAD[$mailField][0] = $userData['email'] ? $userData['email'] : "";
                $adduserAD["userPassword"][0] = $ldapUserPwd;

                //map additional data from frontend group mapping
                $mappingDatas = $ldapFrontendGroups[0]->getFieldMapping();
                if ($mappingDatas) {
                    foreach ($mappingDatas as $mappingData) {
                        $contaoField = $mappingData['contaoField'];
                        if ($contaoField == "") {
                            continue;
                        }
                        $ldapField = $mappingData['ldapField'];
                        $ldapFieldData = $userData[$contaoField];
                        if ($contaoField == 'country') {
                            $ldapFieldData = strtolower($ldapFieldData);
                        }
                        if (!$ldapFieldData || $ldapFieldData == '') {
                            $ldapFieldData = ' ';
                        }
                        $adduserAD[$ldapField][0] = $ldapFieldData;
                    }
                }

                //connect to ldap server
                $ldapConnection = new LdapConnection();
                $ldap = $ldapConnection->ldapConnect();
                $bind = $ldapConnection->ldapBind($ldap);

                //ToDo: check if user is already on the ldap server

                //add to ldap server
                ldap_add($ldap, $userRDNKey."=".$userRDNObject.",".$module->c4gLdapRegistrationOu, $adduserAD);
                $ldapError = ldap_error($ldap);
                if ($ldapError != "Success") {
                    if ($ldapError == "Invalid syntax") {
                        $ldapError = $ldapError.", möglicherweise wurden Felder an den FrontEnd-Gruppen verknüpft, die nicht als Feld im LDAP-Server angelegt werden dürfen.";
                    }
                    \System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim Erstellen des LDAP-Eintrags: '.$ldapError, array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                            )));
                    ldap_unbind($ldap);
                    return false;
                }

                ldap_unbind($ldap);

                $newMember = LdapMemberModel::findById($userId);
                if ($newMember) {
                    $newMember->con4gisLdapMember = 1;
                    $newMember->password = $ldapConnection->generatePassword();
                    $newMember->save();
                }

                $test = "test";
            }
        }
    }
}