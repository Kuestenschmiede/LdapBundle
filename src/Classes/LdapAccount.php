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
use Contao\Module;
use con4gis\LdapBundle\Classes\LdapConnection;
use Contao\System;
use League\Uri\Data;
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
            $ldapRegistration = $ldapSettings[0]->getC4gLdapRegistration();

            if ($ldapRegistration == "1") {
                $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
                $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();

                if (empty($ldapSettings)) {
                    \System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim Finden der allgemeinen LDAP Einstellungen.', array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                            )));
                    return false;
                }

                $baseDn = $ldapSettings[0]->getBaseDn();
                $serverType = $ldapSettings[0]->getServerType();
                $groupFilter = $ldapSettings[0]->getGroupFilter();
                if (!$groupFilter) {
                    $groupFilter = "(|(cn=*)(uid=*))";
                }
                $mailField = strtolower($ldapSettings[0]->getEmail());
                $firstnameField = strtolower($ldapSettings[0]->getFirstname());
                $lastnameField = strtolower($ldapSettings[0]->getLastname());

                if ($serverType == "windows_ad") {
                    $userRDNKey = "CN";
                    $userRDNObject = $userData['firstname']." ".$userData['lastname'];
                    $adduserAD["cn"][0] = $userRDNObject;
                    $adduserAD["objectclass"][0] = "user";
                    $adduserAD["sAMAccountName"][0] = $userData['username'];
                    //ToDo: add be setting to change UserAccountControl flags
                    //UserAccountControl-Flags
                    /*
                     * 1 => 'SCRIPT',
                       2 => 'ACCOUNTDISABLE',
                       8 => 'HOMEDIR_REQUIRED',
                      16 => 'LOCKOUT',
                      32 => 'PASSWD_NOTREQD',
                      64 => 'PASSWD_CANT_CHANGE',
                     128 => 'ENCRYPTED_TEXT_PWD_ALLOWED',
                     256 => 'TEMP_DUPLICATE_ACCOUNT',
                     512 => 'NORMAL_ACCOUNT',
                    2048 => 'INTERDOMAIN_TRUST_ACCOUNT',
                    4096 => 'WORKSTATION_TRUST_ACCOUNT',
                    8192 => 'SERVER_TRUST_ACCOUNT',
                   65536 => 'DONT_EXPIRE_PASSWORD',
                  131072 => 'MNS_LOGON_ACCOUNT',
                  262144 => 'SMARTCARD_REQUIRED',
                  524288 => 'TRUSTED_FOR_DELEGATION',
                 1048576 => 'NOT_DELEGATED',
                 2097152 => 'USE_DES_KEY_ONLY',
                 4194304 => 'DONT_REQ_PREAUTH',
                 8388608 => 'PASSWORD_EXPIRED',
                16777216 => 'TRUSTED_TO_AUTH_FOR_DELEGATION',
                67108864 => 'PARTIAL_SECRETS_ACCOUNT'
                    */
                    $adduserAD["UserAccountControl"][0] = 65536+512+2;
                    $ADPass = "\"" . $_POST['password'] . "\"";
                    $ADPass = mb_convert_encoding($ADPass, "UTF-16LE");
                } else {
                    $userRDNKey = "uid";
                    $userRDNObject = $userData['username'];
                    //ToDo: check if username is not null
                    $adduserAD["cn"][0] = $userData['username'];
                    $adduserAD["objectclass"][0] = "inetOrgPerson";
                    //hash the pwd
                    $userPwdPlain = $_POST['password'];
                    $userPwdHash = base64_encode(hash("sha512", $userPwdPlain, true));
                    $ldapUserPwd = "{SHA512}".$userPwdHash;
                    $adduserAD["userPassword"][0] = $ldapUserPwd;
                }

                //create array for new ldap entry
                //ToDo: check if username is not null
                $adduserAD["uid"][0] = $userData['username'];
                $adduserAD["objectclass"][1] = "person";
                $adduserAD["objectclass"][2] = "organizationalPerson";
                $adduserAD["objectclass"][3] = "top";
                $adduserAD[$firstnameField][0] = $userData['firstname'] ? $userData['firstname'] : "";
                $adduserAD[$lastnameField][0] = $userData['lastname'] ? $userData['lastname'] : "";
                $adduserAD[$mailField][0] = $userData['email'] ? $userData['email'] : "";

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

                //add user to ldap server
                $ldapRegistrationOu = $ldapSettings[0]->getC4gLdapRegistrationOu();
                $userDn = $userRDNKey."="."$userRDNObject".",".$ldapRegistrationOu;
                ldap_add($ldap, $userDn, $adduserAD);
                $ldapError = ldap_error($ldap);
                if ($ldapError != "Success" && !is_null($ldapError)) {
                    if ($ldapError == "Invalid syntax") {
                        $ldapError = $ldapError.", möglicherweise wurden Felder an den FrontEnd-Gruppen verknüpft, die nicht als Feld im LDAP-Server angelegt werden dürfen.";
                    }
                    if ($ldapError == "Already exists") {
                        \System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Der Benuzter '.$userData['username'].' existiert bereits im LDAP.', array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                                )));
                        $user = LdapMemberModel::findByUsername($userData['username']);
                        $user->delete();
                    } else {
                        \System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Fehler beim Erstellen des LDAP-Eintrags: '.$ldapError, array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                                )));
                        $user = LdapMemberModel::findByUsername($userData['username']);
                        $user->delete();
                    }
                    ldap_unbind($ldap);
                    return false;
                }

                if ($serverType == "windows_ad") {
                    $passwordEntry = array('unicodePwd' => $ADPass);
                    $passwordEntry["lockouttime"][0]=0;
                    ldap_mod_replace($ldap, $userDn, $passwordEntry);
                    $passwordError = ldap_error($ldap);
                    if ($passwordError != "Success" && !is_null($passwordError)) {
                        \System::getContainer()
                            ->get('monolog.logger.contao')
                            ->log(LogLevel::ERROR, 'Fehler beim setzen des Passworts im Active Directory: '.$passwordError, array(
                                'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                                )));
                    }
                }

                //check for connected ldap groups
                $groups = unserialize($module->reg_groups);
                foreach ($groups as $group) {
                    $group = $db->prepare("SELECT name FROM tl_member_group WHERE con4gisLdapMemberGroup=1 AND id=?")
                        ->execute($group)->fetchAssoc();
                    if ($group) {
                        $registeredGroups[] = $group['name'];
                    }
                }

                //adding registered ldap groups
                if (isset($registeredGroups)) {
                    $ldapGroups = ldap_search($ldap, $baseDn, $groupFilter);
                    if ($ldapGroups) {
                        $ldapGroups = ldap_get_entries($ldap, $ldapGroups);
                        if ($ldapGroups) {
                            unset($ldapGroups['count']);
                            foreach ($ldapGroups as $ldapGroup) {
                                $groupDn = $ldapGroup['dn'];
                                $rdnArray = explode(",", $groupDn);
                                $rdnFirstObject = str_replace("=", "", strstr($rdnArray[0], "="));
                                if (in_array($rdnFirstObject, $registeredGroups)) {
                                    //Add this group to registered member
                                    //ToDo: check if user is already in the group
                                    $newGroupEntry['member'][0] = $userDn;
                                    ldap_mod_add($ldap, $groupDn, $newGroupEntry);
                                }
                            }
                        }
                    }
                }

                //close ldap connection
                ldap_unbind($ldap);

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
        if ($ldapMember->con4gisLdapMember == '1') {
            //connect to ldap server
            $ldapConnection = new LdapConnection();
            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            //get ldap settings
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapSettings = $ldapSettingsRepo->findAll();
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
//            $updateEntry = array('unicodePwd' => $ADPass);
            if ($serverType == "windows_ad") {
                $updateEntry["UserAccountControl"][0] = 65536+512;
                ldap_mod_replace($ldap, $userDn, $updateEntry);
                $updateError = ldap_error($ldap);
                if ($updateError != "Success" && !is_null($updateError)) {
                    \System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim Aktivieren des LDAP-Kontos: '.$updateError, array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                            )));
                }
            }

            $test = "";
        }
    }

//    private function ADUnicodePwdValue($plain_txt_value)
//    {
//        // This requires recode to be installed on your webserver
//        // If this isn't possible, look up alternate ways of formatting unicodePwd in PHP
//        return str_replace("\n", "", shell_exec("echo -n '\"" . $plain_txt_value . "\"' | recode latin1..utf-16le/base64"));
//    }
}