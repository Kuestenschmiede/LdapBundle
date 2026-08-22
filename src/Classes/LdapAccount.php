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
use Contao\Database;
use Contao\Input;
use Contao\Module;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LogLevel;
use Contao\CoreBundle\Monolog\ContaoContext;

class LdapAccount
{
    public function onAccountCreation(int $userId, array $userData, Module $module) {
        $db = Database::getInstance();
        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');

        if ($module && $userData && $userId) {
            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapSettings = $ldapSettingsRepo->findAll();
            if (empty($ldapSettings)) {
                return false;
            }
            $ldapRegistration = $ldapSettings[0]->getC4gLdapRegistration();

            if ($ldapRegistration == "1") {
                $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
                $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();

                $baseDn = $ldapSettings[0]->getBaseDn();
                $serverType = $ldapSettings[0]->getServerType();
                $groupFilter = $ldapSettings[0]->getGroupFilter();
                if (!$groupFilter) {
                    $groupFilter = "(|(cn=*)(uid=*))";
                }
                $mailField = strtolower($ldapSettings[0]->getEmail());
                $firstnameField = strtolower($ldapSettings[0]->getFirstname());
                $lastnameField = strtolower($ldapSettings[0]->getLastname());

                $rawPassword = Input::post('password') ?: ($userData['password'] ?? '');

                if ($serverType == "windows_ad") {
                    $userRDNKey = "CN";
                    $userRDNObject = $userData['firstname']." ".$userData['lastname'];
                    $adduserAD["cn"][0] = $userRDNObject;
                    $adduserAD["objectclass"][0] = "user";
                    $adduserAD["sAMAccountName"][0] = $userData['username'];
                    $adduserAD["UserAccountControl"][0] = 65536+512+2;
                    $ADPass = "\"" . $rawPassword . "\"";
                    $ADPass = mb_convert_encoding($ADPass, "UTF-16LE");
                } else {
                    $userRDNKey = "uid";
                    $userRDNObject = $userData['username'];
                    $adduserAD["cn"][0] = $userData['username'];
                    $adduserAD["objectclass"][0] = "inetOrgPerson";
                    //hash the pwd
                    $userPwdPlain = $rawPassword;
                    $userPwdHash = base64_encode(hash("sha512", $userPwdPlain, true));
                    $ldapUserPwd = "{SHA512}".$userPwdHash;
                    $adduserAD["userPassword"][0] = $ldapUserPwd;
                }

                //create array for new ldap entry
                $adduserAD["uid"][0] = $userData['username'];
                $adduserAD["objectclass"][1] = "person";
                $adduserAD["objectclass"][2] = "organizationalPerson";
                $adduserAD["objectclass"][3] = "top";
                $adduserAD[$firstnameField][0] = $userData['firstname'] ? $userData['firstname'] : "";
                $adduserAD[$lastnameField][0] = $userData['lastname'] ? $userData['lastname'] : "";
                $adduserAD[$mailField][0] = $userData['email'] ? $userData['email'] : "";

                //map additional data from frontend group mapping
                if (!empty($ldapFrontendGroups)) {
                    $mappingDatas = $ldapFrontendGroups[0]->getFieldMapping();
                    if ($mappingDatas) {
                        foreach ($mappingDatas as $mappingData) {
                            $contaoField = $mappingData['contaoField'];
                            if ($contaoField == "") {
                                continue;
                            }
                            $ldapField = $mappingData['ldapField'];
                            $ldapFieldData = $userData[$contaoField] ?? '';
                            if ($contaoField == 'country') {
                                $ldapFieldData = strtolower($ldapFieldData);
                            }
                            if (!$ldapFieldData || $ldapFieldData == '') {
                                $ldapFieldData = ' ';
                            }
                            $adduserAD[$ldapField][0] = $ldapFieldData;
                        }
                    }
                }

                //connect to ldap server
                $ldapConnection = new LdapConnection();
                $ldap = $ldapConnection->ldapConnect();
                $bind = $ldapConnection->ldapBind($ldap);

                //add user to ldap server
                $ldapRegistrationOu = $ldapSettings[0]->getC4gLdapRegistrationOu();
                $userDn = $userRDNKey."="."$userRDNObject".",".$ldapRegistrationOu;
                @ldap_add($ldap, $userDn, $adduserAD);
                $ldapError = ldap_error($ldap);
                if ($ldapError != "Success" && !is_null($ldapError)) {
                    if ($ldapError == "Invalid syntax") {
                        $ldapError = $ldapError.", möglicherweise wurden Felder an den FrontEnd-Gruppen verknüpft, die nicht als Feld im LDAP-Server angelegt werden dürfen.";
                    }
                    if ($ldapError == "Already exists") {
                        System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Der Benutzer '.$userData['username'].' existiert bereits im LDAP.', array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, ContaoContext::ACCESS
                                )));
                        $user = LdapMemberModel::findByUsername($userData['username']);
                        if ($user) {
                            $user->delete();
                        }
                    } else {
                        System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Fehler beim Erstellen des LDAP-Eintrags: '.$ldapError, array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, ContaoContext::ACCESS
                                )));
                        $user = LdapMemberModel::findByUsername($userData['username']);
                        if ($user) {
                            $user->delete();
                        }
                    }
                    @ldap_unbind($ldap);
                    return false;
                }

                if ($serverType == "windows_ad") {
                    $passwordEntry = array('unicodePwd' => $ADPass);
                    $passwordEntry["lockouttime"][0]=0;
                    @ldap_mod_replace($ldap, $userDn, $passwordEntry);
                    $passwordError = ldap_error($ldap);
                    if ($passwordError != "Success" && !is_null($passwordError)) {
                        System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Fehler beim setzen des Passworts im Active Directory: '.$passwordError, array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, ContaoContext::ACCESS
                                )));
                    }
                }

                //check for connected ldap groups
                $groups = StringUtil::deserialize($module->reg_groups, true);
                $registeredGroups = [];
                foreach ($groups as $group) {
                    $group = $db->prepare("SELECT name FROM tl_member_group WHERE con4gisLdapMemberGroup=1 AND id=?")
                        ->execute($group)->fetchAssoc();
                    if ($group) {
                        $registeredGroups[] = $group['name'];
                    }
                }

                //adding registered ldap groups
                if (!empty($registeredGroups)) {
                    $ldapGroups = @ldap_search($ldap, $baseDn, $groupFilter);
                    if ($ldapGroups) {
                        $ldapGroups = $ldap && $ldapGroups ? ldap_get_entries($ldap, $ldapGroups) : false;
                        if ($ldapGroups) {
                            unset($ldapGroups['count']);
                            foreach ($ldapGroups as $ldapGroup) {
                                $groupDn = $ldapGroup['dn'];
                                $rdnArray = explode(",", $groupDn);
                                $rdnFirstObject = str_replace("=", "", strstr($rdnArray[0], "="));
                                if (in_array($rdnFirstObject, $registeredGroups)) {
                                    $newGroupEntry['member'][0] = $userDn;
                                    @ldap_mod_add($ldap, $groupDn, $newGroupEntry);
                                }
                            }
                        }
                    }
                }

                //close ldap connection
                @ldap_unbind($ldap);

                $newMember = LdapMemberModel::findById($userId);
                if ($newMember) {
                    $newMember->con4gisLdapMember = 1;
                    $newMember->password = $ldapConnection->generatePassword();
                    $newMember->save();
                }
            }
        }
    }

    public function onAccountActivation($member, Module $module): void
    {
        $ldapMember = LdapMemberModel::findByUsername($member->username);
        if ($ldapMember && $ldapMember->con4gisLdapMember == '1') {
            //connect to ldap server
            $ldapConnection = new LdapConnection();
            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            //get ldap settings
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapSettings = $ldapSettingsRepo->findAll();
            if (empty($ldapSettings)) {
                return;
            }
            $ldapRegistrationOu = $ldapSettings[0]->getC4gLdapRegistrationOu();
            $serverType = $ldapSettings[0]->getServerType();
            if ($serverType == "windows_ad") {
                $userRDNKey = "CN";
                $userRDNObject = $ldapMember->firstname." ".$ldapMember->lastname;
            } else {
                $userRDNKey = "uid";
                $userRDNObject = $ldapMember->username;
            }

            //activate user on ldap server
            $userDn = $userRDNKey."="."$userRDNObject".",".$ldapRegistrationOu;
            if ($serverType == "windows_ad") {
                $updateEntry["UserAccountControl"][0] = 65536+512;
                @ldap_mod_replace($ldap, $userDn, $updateEntry);
                $updateError = ldap_error($ldap);
                if ($updateError != "Success" && !is_null($updateError)) {
                    System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim Aktivieren des LDAP-Kontos: '.$updateError, array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, ContaoContext::ACCESS
                            )));
                }
            }
            @ldap_unbind($ldap);
        }
    }
}