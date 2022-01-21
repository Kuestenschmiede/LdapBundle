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
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Database;
use Contao\Module;
use Contao\FrontendUser;
use Contao\System;
use Psr\Log\LogLevel;

class MemberAccountData
{
    public function updateMemberData(FrontendUser $member, array $data, Module $module) {
        $ldapRegistration = $module->c4gLdapRegistration;
        if ($ldapRegistration == "1") {
            $db = Database::getInstance();
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');

            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);

            $ldapSettings = $ldapSettingsRepo->findAll();
            $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();

            $baseDn = $ldapSettings[0]->getBaseDn();
            $serverType = $ldapSettings[0]->getServerType();
            $groupFilter = $ldapSettings[0]->getGroupFilter();
            $mailField = strtolower($ldapSettings[0]->getEmail());
            $firstnameField = strtolower($ldapSettings[0]->getFirstname());
            $lastnameField = strtolower($ldapSettings[0]->getLastname());
            $ldapRegistrationOu = $ldapSettings[0]->getC4gLdapRegistrationOu();

            if ($serverType == "windows_ad") {
                $userRDNKey = "CN";
                $userRDNObject = $member->firstname." ".$member->lastname;
            } else {
                $userRDNKey = "uid";
                $userRDNObject = $member->username;
            }

            $mappingDatas = $ldapFrontendGroups[0]->getFieldMapping();
            if (!$mappingDatas) {
                $mappingDatas[0]['contaoField'] = "firstname";
                $mappingDatas[0]['ldapField'] = $firstnameField;
                $mappingDatas[1]['contaoField'] = "lastname";
                $mappingDatas[1]['ldapField'] = $lastnameField;
                $mappingDatas[2]['contaoField'] = "mail";
                $mappingDatas[2]['ldapField'] = $mailField;
            } else {
                $lastMappingDatasKey = array_key_last($mappingDatas);
                $mappingDatas[$lastMappingDatasKey + 1]['contaoField'] = "firstname";
                $mappingDatas[$lastMappingDatasKey + 1]['ldapField'] = $firstnameField;
                $mappingDatas[$lastMappingDatasKey + 2]['contaoField'] = "lastname";
                $mappingDatas[$lastMappingDatasKey + 2]['ldapField'] = $lastnameField;
                $mappingDatas[$lastMappingDatasKey + 3]['contaoField'] = "mail";
                $mappingDatas[$lastMappingDatasKey + 3]['ldapField'] = $mailField;
            }
            foreach ($mappingDatas as $mappingData) {
                if (array_key_exists($mappingData['contaoField'], $data)) {
                    $updateEntry[$mappingData['ldapField']][0] = $data[$mappingData['contaoField']];
                }
            }

            //connect to ldap server
            $ldapConnection = new LdapConnection();
            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            //edit user in ldap
            $userDn = $userRDNKey."=".$userRDNObject.",".$ldapRegistrationOu;
            ldap_mod_replace($ldap, $userDn, $updateEntry);
            $ldapError = ldap_error($ldap);
            if ($ldapError != "Success" && !is_null($ldapError)) {
                \System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::ERROR, 'Fehler beim Aktualisieren des Passworts: '.$ldapError, array(
                        'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                        )));
            }

            //close ldap connection
            ldap_unbind($ldap);
            ldap_close($ldap);
        }
    }

    public function updateMemberPassword($member, string $password, Module $module = null) {
        //get ldap settings
        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();
        $serverType = $ldapSettings[0]->getServerType();
        $ldapRegistrationOu = $ldapSettings[0]->getC4gLdapRegistrationOu();
        $twoDirectionalSync = $ldapSettings[0]->getTwoDirectionalSync();

        if ($twoDirectionalSync == "1") {
            //get new user password
            $userPwdPlain = $_POST['password'];

            //get user dn and password entry
            if ($serverType == "windows_ad") {
                $userRDNKey = "CN";
                $userRDNObject = $member->firstname." ".$member->lastname;

                $ADPass = "\"" . $userPwdPlain . "\"";
                $ADPass = mb_convert_encoding($ADPass, "UTF-16LE");
                $updateEntry = array('unicodePwd' => $ADPass);
                $updateEntry["lockouttime"][0]=0;
            } else {
                $userRDNKey = "uid";
                $userRDNObject = $member->username;

                $userPwdHash = base64_encode(hash("sha512", $userPwdPlain, true));
                $ldapUserPwd = "{SHA512}".$userPwdHash;

                $updateEntry["userPassword"][0] = $ldapUserPwd;
            }

            //connect to ldap server
            $ldapConnection = new LdapConnection();
            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            //edit user in ldap
            $userDn = $userRDNKey."=".$userRDNObject.",".$ldapRegistrationOu;
            ldap_mod_replace($ldap, $userDn, $updateEntry);
            $ldapError = ldap_error($ldap);
            if ($ldapError != "Success" && !is_null($ldapError)) {
                \System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::ERROR, 'Fehler beim Aktualisieren des Passworts: '.$ldapError, array(
                        'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                        )));
            }

            //set random contao member password
            $member = LdapMemberModel::findByUsername($member->username);
            $member->password = $ldapConnection->generatePassword();
            $member->save();

            //close ldap connection
            ldap_unbind($ldap);
            ldap_close($ldap);
        }
    }
}