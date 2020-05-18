<?php

namespace con4gis\AuthBundle\Classes;

class LdapConnection
{
    public function getLdapUserGroups($em, $loginUsername, $authBeGroups, $authSettings)
    {
        //Check if Login User is in Admin Group
        $bindDn = $authSettings[0]->getBindDn();
        $baseDn = $authSettings[0]->getBaseDn();
        $password = $authSettings[0]->getPassword();
        $encryption = $authSettings[0]->getEncryption();
        $server = $authSettings[0]->getServer();
        $port = $authSettings[0]->getPort();
        $groups = [];

        $userFilter = '(&(' . $authSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

        if ($encryption == 'ssl') {
            $adServer = 'ldaps://' . $server . ':' . $port;
        } elseif ($encryption == 'plain') {
            $adServer = 'ldap://' . $server . ':' . $port;
        }

        $ldap = ldap_connect($adServer);

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        if ($bind) {
            if ($userFilter) {
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

    public function ldapBind($ldap, $bindDn, $password)
    {
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        return $bind;
    }

    public function filterLdap($bindDn, $password, $filter, $baseDn, $adServer)
    {
        $ldap = ldap_connect($adServer);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        if ($bind) {
            $result = ldap_search($ldap, $baseDn, $filter);

            return $ldapUser = ldap_get_entries($ldap, $result);
        }

        return false;
    }
}
