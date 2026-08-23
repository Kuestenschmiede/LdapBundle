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
 */
namespace con4gis\LdapBundle\Classes\Listener;

use con4gis\LdapBundle\Classes\LdapConnection;
use con4gis\LdapBundle\Entity\Con4gisLdapSettings;
use con4gis\LdapBundle\Resources\contao\models\LdapUserModel;
use con4gis\LdapBundle\Resources\contao\models\LdapMemberModel;
use Contao\BackendUser;
use Contao\FrontendUser;
use Contao\MemberGroupModel;
use Contao\StringUtil;
use Contao\System;
use Contao\UserGroupModel;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use con4gis\LdapBundle\Entity\Con4gisLdapBackendGroups;
use Contao\Database;

class LoginListener
{
    private $db = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if (!$user) {
            return false;
        }

        $loginUsername = method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : (method_exists($token, 'getUserIdentifier') ? $token->getUserIdentifier() : $token->getUsername());
        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapConnection = new LdapConnection();

        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();
        if (empty($ldapSettings)) {
            return false;
        }
        $encryption = $ldapSettings[0]->getEncryption();
        $server = $ldapSettings[0]->getServer();
        $port = $ldapSettings[0]->getPort();
        $bindDn = $ldapSettings[0]->getBindDn();
        $baseDn = $ldapSettings[0]->getBaseDn();
        $bindPassword = $ldapSettings[0]->getPassword();
        $mailField = strtolower($ldapSettings[0]->getEmail());
        $firstnameField = strtolower($ldapSettings[0]->getFirstname());
        $lastnameField = strtolower($ldapSettings[0]->getLastname());
        $userFilter = '(&(' . $ldapSettings[0]->getUserFilter() . '=' . $loginUsername . '))';

        $adServer = 'ldap://' . $server . ':' . $port;
        if ($encryption == 'ssl') {
            $adServer = 'ldaps://' . $server . ':' . $port;
        } elseif ($encryption == 'plain' || $encryption == 'tls') {
            $adServer = 'ldap://' . $server . ':' . $port;
        }

        if ($user instanceof BackendUser) {
            $beUser = LdapUserModel::findByUsername($loginUsername);

            if ($beUser && $beUser->con4gisLdapUser == '1') {
                //Get LDAP Admin Group
                $ldapBeGroupsRepo = $em->getRepository(Con4gisLdapBackendGroups::class);
                $ldapBeGroups = $ldapBeGroupsRepo->findAll();

                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();

                $adminGroup = !empty($ldapBeGroups) ? $ldapBeGroups[0]->getAdminGroup() : [];
                $arrAdminGroups = is_array($adminGroup) ? $adminGroup : StringUtil::deserialize($adminGroup, true);

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $ldapSettings);

                $contaoGroups = [];

                foreach ($groups as $group) {
                    if ($foundGroup = UserGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $beUser->groups = serialize($contaoGroups);
                    $beUser->tstamp = time();
                }

                $beUser->con4gisLdapUser = '1';

                $isAdmin = false;
                foreach ($groups as $group) {
                    if (in_array($group, $arrAdminGroups)) {
                        $isAdmin = true;
                        break;
                    }
                }
                $beUser->admin = $isAdmin ? '1' : '0';
                $beUser->tstamp = time();

                if ($beUser->name == '' || $beUser->email == '') {
                    $ldapUser = $ldapConnection->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
                    if (!empty($ldapUser) && isset($ldapUser[0])) {
                        $userMail = !empty($mailField) && isset($ldapUser[0][$mailField][0]) ? $ldapUser[0][$mailField][0] : '';
                        $userFirstname = !empty($firstnameField) && isset($ldapUser[0][$firstnameField][0]) ? $ldapUser[0][$firstnameField][0] : '';
                        $userLastname = !empty($lastnameField) && isset($ldapUser[0][$lastnameField][0]) ? $ldapUser[0][$lastnameField][0] : '';

                        if ($userMail) {
                            $beUser->email = $userMail;
                        }

                        if ($userFirstname && $userLastname) {
                            $beUser->name = $userFirstname . ' ' . $userLastname;
                        }
                    }
                }

                $beUser->save();
            }
        } elseif ($user instanceof FrontendUser) {
            $feUser = LdapMemberModel::findByUsername($loginUsername);

            if ($feUser && $feUser->con4gisLdapMember == '1') {
                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $ldapSettings);

                $contaoGroups = [];

                foreach ($groups as $group) {
                    if ($foundGroup = MemberGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $feUser->groups = serialize($contaoGroups);
                    $feUser->tstamp = time();
                }

                $feUser->con4gisLdapMember = '1';

                if ($feUser->firstname == '' || $feUser->lastname == '' || $feUser->email == '') {
                    $ldapUser = $ldapConnection->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
                    if (!empty($ldapUser) && isset($ldapUser[0])) {
                        $userMail = !empty($mailField) && isset($ldapUser[0][$mailField][0]) ? $ldapUser[0][$mailField][0] : '';
                        $userFirstname = !empty($firstnameField) && isset($ldapUser[0][$firstnameField][0]) ? $ldapUser[0][$firstnameField][0] : '';
                        $userLastname = !empty($lastnameField) && isset($ldapUser[0][$lastnameField][0]) ? $ldapUser[0][$lastnameField][0] : '';

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
                }

                $feUser->save();
            }
        }
    }

    public function generatePassword()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';

        return hash('sha384', substr(str_shuffle($chars), 0, 18));
    }
}
