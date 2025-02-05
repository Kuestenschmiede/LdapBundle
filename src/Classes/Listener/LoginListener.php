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
use Contao\User;
use Contao\UserGroupModel;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use con4gis\LdapBundle\Entity\Con4gisLdapBackendGroups;
use Contao\Database;

class LoginListener extends System
{
    private $db = null;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $loginUsername = $event->getAuthenticationToken()->getUsername();
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

        if ($encryption == 'ssl') {
            $adServer = 'ldaps://' . $server . ':' . $port;
        } elseif ($encryption == 'plain' || $encryption == 'tls') {
            $adServer = 'ldap://' . $server . ':' . $port;
        }

        if (TL_MODE == 'BE') {
            $beUser = LdapUserModel::findByUsername($loginUsername);

            if ($beUser && $beUser->con4gisLdapUser == '1') {

                //Get LDAP Admin Group
                $ldapBeGroupsRepo = $em->getRepository(Con4gisLdapBackendGroups::class);
                $ldapBeGroups = $ldapBeGroupsRepo->findAll();

                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();

                $adminGroup = $ldapBeGroups[0]->getAdminGroup();
                
                $arrAdminGroups = StringUtil::deserialize($adminGroup, true);

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $ldapSettings);

                $contaoGroups = [];

                $beUser = BackendUser::getInstance();

                foreach ($groups as $group) {
                    if ($foundGroup = UserGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $contaoGroups = serialize($contaoGroups);
                    $beUser->groups = $contaoGroups;
                    $beUser->tstamp = time();
                }

                if ($beUser->con4gisLdapUser == '0') {
                    $beUser->con4gisLdapUser = '1';
                }

                $user = LdapUserModel::findByUsername($loginUsername);
                if ($user) {
                    foreach ($groups as $group) {
                        
                        if (in_array($group, $arrAdminGroups)) {
                            $beUser->admin = '1';
                            $beUser->tstamp = time();

                            break;
                        }
                        
                        $beUser->admin = '0';
                        $beUser->tstamp = time();
                    }
                }

                if ($beUser->name == '' or $beUser->email == '') {
                    $ldapUser = $ldapConnection->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
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

                $beUser->password = $this->generatePassword();
            }
        } elseif (TL_MODE == 'FE') {
            $feUser = LdapMemberModel::findByUsername($loginUsername);

            if ($feUser && $feUser->con4gisLdapMember == '1') {
                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $ldapSettings);

                $contaoGroups = [];

                $feUser = FrontendUser::getInstance();

                foreach ($groups as $group) {
                    if ($foundGroup = MemberGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $contaoGroups = serialize($contaoGroups);
                    $feUser->allGroups = $contaoGroups;
                    $feUser->tstamp = time();
                }

                if ($feUser->con4gisLdapMember == '0') {
                    $feUser->con4gisLdapMember = '1';
                }

                if ($feUser->firstname == '' || $feUser->lastname || $feUser->email == '') {
                    $ldapUser = $ldapConnection->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);
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

                $feUser->password = $this->generatePassword();

                $feUser->con4gisLdapMember = '1';
            }
        }
    }

    public function generatePassword()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';

        return hash('sha384', substr(str_shuffle($chars), 0, 18));
    }
}
