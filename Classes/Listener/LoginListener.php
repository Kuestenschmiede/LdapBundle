<?php


namespace con4gis\AuthBundle\Classes\Listener;


use con4gis\AuthBundle\Entity\Con4gisAuthSettings;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\System;
use Contao\UserGroupModel;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Contao\UserModel;
use Contao\Model;
use con4gis\AuthBundle\Entity\Con4gisAuthBackendGroups;
use Contao\Database;

class LoginListener extends System
{

    private $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function onInteractiveLogin (InteractiveLoginEvent $event) {

        if (TL_MODE == 'BE') {

            $loginUsername = $event->getAuthenticationToken()->getUsername();
            $beUser = UserModel::findByUsername($loginUsername);

            if ($beUser) {

                //Get LDAP Admin Group
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $authBeGroupsRepo = $em->getRepository(Con4gisAuthBackendGroups::class);
                $authBeGroups = $authBeGroupsRepo->findAll();
                $adminGroup = $authBeGroups[0]->getAdminGroup();

                $groups = $this->getLdapUserGroups($em, $loginUsername, $authBeGroups);

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

                $user = UserModel::findByUsername($loginUsername);
                if ($user) {
                    foreach ($groups as $group) {
                        if ($group == $adminGroup) {
//                            $sql = $this->db->prepare("UPDATE tl_user SET admin='1' WHERE id=?")->execute($beUser->id);
                            $beUser->admin = '1';
                            $beUser->tstamp = time();
                            break;
                        } else {
                            $beUser->admin = '0';
                            $beUser->tstamp = time();
                        }
                    }
                }

                echo "test";

            } else {

                //Get LDAP Admin Group
                $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
                $authBeGroupsRepo = $em->getRepository(Con4gisAuthBackendGroups::class);
                $authBeGroups = $authBeGroupsRepo->findAll();
                $adminGroup = $authBeGroups[0]->getAdminGroup();

                $encryption = $authBeGroups[0]->getEncryption();
                $server = $authBeGroups[0]->getServer();
                $port = $authBeGroups[0]->getPort();
                $bindDn = $authBeGroups[0]->getBindDn();
                $baseDn = $authBeGroups[0]->getBaseDn();
                $password = $authBeGroups[0]->getPassword();

                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
                $authSettings = $authSettingsRepo->findAll();
                $mailField = $authSettings[0]->getEmail();
                $nameField = $authSettings[0]->getName();
                $userFilter = "(&(".$authSettings[0]->getUserFilter()."=".$loginUsername."))";

                if ($encryption == 'ssl') {
                    $adServer = "ldaps://" . $server . ":" . $port;
                } else if ($encryption == 'plain') {
                    $adServer = "ldap://" . $server . ":" . $port;
                }

                $ldapUser = $this->filterLdap($bindDn, $password, $userFilter, $baseDn, $adServer);
                $userMail = $ldapUser[0][$mailField][0];
                $userFullName = $ldapUser[0][$nameField][0];

                $groups = $this->getLdapUserGroups($em, $loginUsername, $authBeGroups);

                $user = new UserModel();
                $user->username = $loginUsername;
//                $user->save();
//                $beUser = BackendUser::getInstance();
                if ($userMail) {
                    $user->email = $userMail;
                }

                if ($userFullName) {
                    $user->name = $userFullName;
                }

                if ($user->admin == '1') {
                    return "";
                }

                $contaoGroups = [];

                foreach ($groups as $group) {
                    if ($foundGroup = UserGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $user->groups = serialize($contaoGroups);
                }

                $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-";
                $password = hash("sha384", substr(str_shuffle($chars), 0, 18));

                $user->password = $password;

                $user->dateAdded = time();
                $user->tstamp = time();

                foreach ($groups as $group) {
                    if ($group == $adminGroup) {
                        $user->admin = 1;
                        $user->groups = NULL;
                        break;
                    }
                }

                $user->save();

                Controller::redirect("/contao/logout");
            }

        } elseif (TL_MODE == 'FE') {
            return "";
        }

    }

    public function getLdapUserGroups($em, $loginUsername, $authBeGroups) {
        //Check if Login User is in Admin Group
        $bindDn = $authBeGroups[0]->getBindDn();
        $baseDn = $authBeGroups[0]->getBaseDn();
        $password = $authBeGroups[0]->getPassword();
        $encryption = $authBeGroups[0]->getEncryption();
        $server = $authBeGroups[0]->getServer();
        $port = $authBeGroups[0]->getPort();
        $groups = [];

        $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
        $authSettings = $authSettingsRepo->findAll();
        $userFilter = "(&(".$authSettings[0]->getUserFilter()."=".$loginUsername."))";

        if ($encryption == 'ssl') {
            $adServer = "ldaps://" . $server . ":" . $port;
        } else if ($encryption == 'plain') {
            $adServer = "ldap://" . $server . ":" . $port;
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

    public function ldapBind($ldap, $bindDn, $password) {

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        return $bind;
    }

    public function filterLdap($bindDn, $password, $filter, $baseDn, $adServer) {

        $ldap = ldap_connect($adServer);
        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        if ($bind) {
            $result = ldap_search($ldap, $baseDn, $filter);
            return $ldapUser = ldap_get_entries($ldap, $result);
        } else {
            return false;
        }
    }
}