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
namespace con4gis\AuthBundle\Classes;

use con4gis\AuthBundle\Entity\Con4gisAuthSettings;
use Contao\System;

class LdapConnection
{
    public function getLdapUserGroups($loginUsername, $authSettings)
    {
        //Check if Login User is in Admin Group

        $baseDn = $authSettings[0]->getBaseDn();

        $groups = [];

        $userFilter = '(&(' . $authSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

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
        $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
        $authSettings = $authSettingsRepo->findAll();

        $bind = false;
        if ($authSettings && count($authSettings) > 0) {
            $bindDn = $authSettings[0]->getBindDn();
            $bindPassword = $authSettings[0]->getPassword();
            if ($ldap) {
                $bind = @ldap_bind($ldap, $bindDn, $bindPassword);
            }
        }
        return $bind;

    }

    public function filterLdap($bindDn, $password, $filter, $baseDn, $adServer)
    {

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
        $authSettings = $authSettingsRepo->findAll();

        if ($authSettings && count($authSettings) > 0) {
            $encryption = $authSettings[0]->getEncryption();
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
        $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
        $authSettings = $authSettingsRepo->findAll();
        $ldap = false;
        if ($authSettings && count($authSettings) > 0) {
            $encryption = $authSettings[0]->getEncryption();
            $server = $authSettings[0]->getServer();
            $port = $authSettings[0]->getPort();

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

