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
use Contao\Database;
use Contao\Module;
use Contao\FrontendUser;
use Contao\System;

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

            if ($serverType == "windows_ad") {
                $userRDNKey = "cn";
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
            $userDn = $userRDNKey."=".$userRDNObject.",".$module->c4gLdapRegistrationOu;
            ldap_mod_replace($ldap, $userDn, $updateEntry);

            //close ldap connection
            ldap_unbind($ldap);

            $test = "test";
        }
    }

    public function updateMemberPassword($member, string $password, Module $module = null) {
        if ($module) {
            $ldapRegistration = $module->c4gLdapRegistration;
            if ($ldapRegistration == "1") {
                //get new user password
                $userPwdPlain = $_POST['password'];
                $userPwdHash = base64_encode(hash("sha512", $userPwdPlain, true));
                $ldapUserPwd = "{SHA512}".$userPwdHash;

                //create updated entry for new password
                $updateEntry["userPassword"][0] = $ldapUserPwd;

                //get user dn
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();
                $serverType = $ldapSettings[0]->getServerType();
                if ($serverType == "windows_ad") {
                    $userRDNKey = "cn";
                    $userRDNObject = $member->firstname." ".$member->lastname;
                } else {
                    $userRDNKey = "uid";
                    $userRDNObject = $member->username;
                }

                //connect to ldap server
                $ldapConnection = new LdapConnection();
                $ldap = $ldapConnection->ldapConnect();
                $bind = $ldapConnection->ldapBind($ldap);

                //edit user in ldap
                $userDn = $userRDNKey."=".$userRDNObject.",".$module->c4gLdapRegistrationOu;
                ldap_mod_replace($ldap, $userDn, $updateEntry);

                //close ldap connection
                ldap_unbind($ldap);
            }
        }
    }
}