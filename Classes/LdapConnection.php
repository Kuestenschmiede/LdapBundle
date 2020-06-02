<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package     con4gis
 * @version     7
 * @author      con4gis contributors (see "authors.txt")
 * @license     LGPL-3.0-or-later
 * @copyright   Küstenschmiede GmbH Software & Design
 * @link        https://www.con4gis.org
 *
 */
namespace con4gis\LdapBundle\Classes;

use con4gis\LdapBundle\Entity\Con4gisLdapSettings;
use Contao\System;

class LdapConnection
{
    public function getLdapUserGroups($loginUsername, $ldapSettings)
    {
        //Check if Login User is in Admin Group

        $baseDn = $ldapSettings[0]->getBaseDn();

        $groups = [];

        $userFilter = '(&(' . $ldapSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

        $ldap = $this->ldapConnect();

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($ldap, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

        $bind = $this->ldapBind($ldap);

        if ($bind) {
            if ($userFilter && $baseDn) {
                $result = ldap_search($ldap, $baseDn, $userFilter);
                $ldapUser = ldap_get_entries($ldap, $result);

                $memberGroups = $ldapUser[0]['memberof'];
                array_shift($memberGroups);
                foreach ($memberGroups as $memberGroup) {
                    $group = strstr($memberGroup, ',', true);
                    $group = trim(substr($group, strpos($group, '=') + 1));
                    $groups[] = $group;
                }
            }
        }

        return $groups;
    }

    public function ldapBind($ldap)
    {

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();

        $bind = false;
        if ($ldapSettings && count($ldapSettings) > 0) {
            $bindDn = $ldapSettings[0]->getBindDn();
            $bindPassword = $ldapSettings[0]->getPassword();
            if ($ldap) {
                $bind = @ldap_bind($ldap, $bindDn, $bindPassword);
            }
        }
        return $bind;

    }

    public function filterLdap($bindDn, $password, $filter, $baseDn, $adServer)
    {

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();

        if ($ldapSettings && count($ldapSettings) > 0) {
            $encryption = $ldapSettings[0]->getEncryption();
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $ldap = ldap_connect($adServer);

        if ($encryption == 'tls') {
            if(!ldap_start_tls($ldap)) {
                return false;
            }
        }

        $bind = @ldap_bind($ldap, $bindDn, $password);

        if ($bind) {
            $result = ldap_search($ldap, $baseDn, $filter);

            return $ldapUser = ldap_get_entries($ldap, $result);
        }

        return false;
    }

    public function ldapConnect()
    {
        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();
        $ldap = false;
        if ($ldapSettings && count($ldapSettings) > 0) {
            $encryption = $ldapSettings[0]->getEncryption();
            $server = $ldapSettings[0]->getServer();
            $port = $ldapSettings[0]->getPort();

            if ($server && $port) {
                if ($encryption == 'ssl') {
                    $adServer = 'ldaps://' . $server . ':' . $port;
                } elseif ($encryption == 'plain' || $encryption == 'tls') {
                    $adServer = 'ldap://' . $server . ':' . $port;
                }

                ldap_set_option(NULL, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

                $ldap = ldap_connect($adServer);

                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

                if ($encryption == 'tls') {
                    if(!ldap_start_tls($ldap)) {
                        return false;
                    }
                }
            }
        }

        return $ldap;
    }

}

