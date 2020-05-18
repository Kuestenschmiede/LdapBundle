<?php

namespace con4gis\AuthBundle\Classes\Listener;

use con4gis\AuthBundle\Entity\Con4gisAuthFrontendGroups;
use con4gis\AuthBundle\Entity\Con4gisAuthSettings;
use Contao\BackendUser;
use Contao\Controller;
use Contao\FrontendUser;
use Contao\MemberGroupModel;
use Contao\MemberModel;
use Contao\System;
use Contao\UserGroupModel;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Contao\UserModel;
use con4gis\AuthBundle\Entity\Con4gisAuthBackendGroups;
use Contao\Database;

class LoginListener extends System
{
    private $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function onSuccessfulAuthentication(AuthenticationEvent $event)
    {
        if (TL_MODE == 'FE') {
            return $event;
        }
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        if (TL_MODE == 'BE') {
            $loginUsername = $event->getAuthenticationToken()->getUsername();
            $beUser = UserModel::findByUsername($loginUsername);

            if ($beUser) {

                //Get LDAP Admin Group
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $authBeGroupsRepo = $em->getRepository(Con4gisAuthBackendGroups::class);
                $authBeGroups = $authBeGroupsRepo->findAll();

                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
                $authSettings = $authSettingsRepo->findAll();

                $adminGroup = $authBeGroups[0]->getAdminGroup();

                $groups = $this->getLdapUserGroups($em, $loginUsername, $authSettings);

                $contaoGroups = [];

                $beUser = BackendUser::getInstance();

                foreach ($groups as $group) {
                    if ($foundGroup = UserGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $contaoGroups = serialize($contaoGroups);
//                    $sql = $this->db->prepare("UPDATE tl_user SET groups=? WHERE id=?")->execute($contaoGroups, $beUser->id);
                    $beUser->groups = $contaoGroups;
                    $beUser->tstamp = time();
                }

                if ($beUser->con4gisAuthUser == '0') {
                    $beUser->con4gisAuthUser = '1';
                }

                $user = UserModel::findByUsername($loginUsername);
                if ($user) {
                    foreach ($groups as $group) {
                        if ($group == $adminGroup) {
//                            $sql = $this->db->prepare("UPDATE tl_user SET admin='1' WHERE id=?")->execute($beUser->id);
                            $beUser->admin = '1';
                            $beUser->tstamp = time();

                            break;
                        }
                        $beUser->admin = '0';
                        $beUser->tstamp = time();
                    }
                }

                if ($beUser->name == '' or $beUser->email == '') {
                    $encryption = $authSettings[0]->getEncryption();
                    $server = $authSettings[0]->getServer();
                    $port = $authSettings[0]->getPort();
                    $bindDn = $authSettings[0]->getBindDn();
                    $baseDn = $authSettings[0]->getBaseDn();
                    $bindPassword = $authSettings[0]->getPassword();
                    $mailField = strtolower($authSettings[0]->getEmail());
                    $firstnameField = strtolower($authSettings[0]->getFirstname());
                    $lastnameField = strtolower($authSettings[0]->getLastname());
                    $userFilter = '(&(' . $authSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

                    if ($encryption == 'ssl') {
                        $adServer = 'ldaps://' . $server . ':' . $port;
                    } elseif ($encryption == 'plain') {
                        $adServer = 'ldap://' . $server . ':' . $port;
                    }

                    $ldapUser = $this->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
                    $userMail = $ldapUser[0][$mailField][0];
                    $userFirstname = $ldapUser[0][$firstnameField][0];
                    $userLastname = $ldapUser[0][$lastnameField][0];

                    if ($userMail) {
                        $beUser->email = $userMail;
                    }

                    if ($userFirstname && $userLastname) {
                        $beUser->name = $userFirstname . ' ' . $userLastname;
                    }
                }

                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';
                $password = hash('sha384', substr(str_shuffle($chars), 0, 18));
                $beUser->password = $password;

                echo 'test';
            }

            //Get LDAP Admin Group
//                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
//                $authBeGroupsRepo = $em->getRepository(Con4gisAuthBackendGroups::class);
//                $authBeGroups = $authBeGroupsRepo->findAll();
//
//                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
//                $authSettings = $authSettingsRepo->findAll();
//
//                $adminGroup = $authBeGroups[0]->getAdminGroup();
//                $encryption = $authSettings[0]->getEncryption();
//                $server = $authSettings[0]->getServer();
//                $port = $authSettings[0]->getPort();
//                $bindDn = $authSettings[0]->getBindDn();
//                $baseDn = $authSettings[0]->getBaseDn();
//                $password = $authSettings[0]->getPassword();
//
//                $mailField = $authSettings[0]->getEmail();
//                $firstnameField = strtolower($authSettings[0]->getFirstname());
//                $lastnameField = strtolower($authSettings[0]->getLastname());
//                $userFilter = "(&(".$authSettings[0]->getUserFilter()."=".$loginUsername."))";
//
//                if ($encryption == 'ssl') {
//                    $adServer = "ldaps://" . $server . ":" . $port;
//                } else if ($encryption == 'plain') {
//                    $adServer = "ldap://" . $server . ":" . $port;
//                }
//
//                $ldapUser = $this->filterLdap($bindDn, $password, $userFilter, $baseDn, $adServer);
//                $userMail = $ldapUser[0][$mailField][0];
//                $userFirstname = $ldapUser[0][$firstnameField][0];
//                $userLastname = $ldapUser[0][$lastnameField][0];
//
//                $groups = $this->getLdapUserGroups($em, $loginUsername, $authSettings);
//
//                $user = new UserModel();
//                $user->username = $loginUsername;
////                $user->save();
////                $beUser = BackendUser::getInstance();
//                if ($userMail) {
//                    $user->email = $userMail;
//                }
//
//                if ($userFirstname && $userLastname) {
//                    $user->name = $userFirstname . " " . $userLastname;
//                }
//
//                if ($user->admin == '1') {
//                    return "";
//                }
//
//                $contaoGroups = [];
//
//                foreach ($groups as $group) {
//                    if ($foundGroup = UserGroupModel::findByName($group)) {
//                        $contaoGroups[] = $foundGroup->id;
//                    }
//                }
//
//                if (!empty($contaoGroups)) {
//                    $user->groups = serialize($contaoGroups);
//                }
//
//                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-";
//                $password = hash("sha384", substr(str_shuffle($chars), 0, 18));
//
//                $user->password = $password;
//
//                $user->dateAdded = time();
//                $user->tstamp = time();
//
//                foreach ($groups as $group) {
//                    if ($group == $adminGroup) {
//                        $user->admin = 1;
//                        $user->groups = NULL;
//                        break;
//                    }
//                }
//
//                $user->save();
//
//                Controller::redirect("/contao/logout");
//                //BackendUser::authenticate();
        } elseif (TL_MODE == 'FE') {
            $loginUsername = $event->getAuthenticationToken()->getUsername();
            $feUser = MemberModel::findByUsername($loginUsername);

            if ($feUser) {

                //Get LDAP Admin Group
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $authFeGroupsRepo = $em->getRepository(Con4gisAuthFrontendGroups::class);
                $authFeGroups = $authFeGroupsRepo->findAll();

                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
                $authSettings = $authSettingsRepo->findAll();

                $groups = $this->getLdapUserGroups($em, $loginUsername, $authSettings);

                $contaoGroups = [];

                $feUser = FrontendUser::getInstance();
//                $feUser = MemberModel::findByUsername($loginUsername);

                foreach ($groups as $group) {
                    if ($foundGroup = MemberGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $contaoGroups = serialize($contaoGroups);
                    $sql = $this->db->prepare('SELECT * FROM tl_member')->execute()->fetchAllAssoc();
                    $feUser->allGroups = $contaoGroups;
                    $feUser->tstamp = time();
                }

                if ($feUser->con4gisAuthMember == '0') {
                    $feUser->con4gisAuthMember = '1';
                }

                $sql = $this->db->prepare('SELECT groups FROM tl_member WHERE id=7')->execute()->fetchAllAssoc();

                if ($feUser->firstname == '' || $feUser->lastname || $feUser->email == '') {
                    $encryption = $authSettings[0]->getEncryption();
                    $server = $authSettings[0]->getServer();
                    $port = $authSettings[0]->getPort();
                    $bindDn = $authSettings[0]->getBindDn();
                    $baseDn = $authSettings[0]->getBaseDn();
                    $bindPassword = $authSettings[0]->getPassword();
                    $mailField = strtolower($authSettings[0]->getEmail());
                    $firstnameField = strtolower($authSettings[0]->getFirstname());
                    $lastnameField = strtolower($authSettings[0]->getLastname());
                    $userFilter = '(&(' . $authSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

                    if ($encryption == 'ssl') {
                        $adServer = 'ldaps://' . $server . ':' . $port;
                    } elseif ($encryption == 'plain') {
                        $adServer = 'ldap://' . $server . ':' . $port;
                    }

                    $ldapUser = $this->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
                    $userMail = $ldapUser[0][$mailField][0];

                    $userFirstname = $ldapUser[0][$firstnameField][0];
                    $userLastname = $ldapUser[0][$lastnameField][0];

                    if ($userMail) {
                        $feUser->email = $userMail;
                    }

                    if ($userFirstname) {
                        $feUser->firstname = $userFirstname;
                    }

                    if ($userLastname) {
                        $feUser->lastname = $userLastname;
                    }
                }

                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';
                $password = hash('sha384', substr(str_shuffle($chars), 0, 18));
                $feUser->password = $password;

                $feUser->con4gisAuthMember = '1';
//                $feUser->save();
                echo 'jklj';
            }
        }
    }

    public function getLdapUserGroups($em, $loginUsername, $authSettings)
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
