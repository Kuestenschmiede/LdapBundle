<?php

namespace con4gis\AuthBundle\Classes\Listener;

use con4gis\AuthBundle\Classes\LdapConnection;
use con4gis\AuthBundle\Entity\Con4gisAuthFrontendGroups;
use con4gis\AuthBundle\Entity\Con4gisAuthSettings;
use con4gis\AuthBundle\Resources\contao\models\AuthUserModel;
use con4gis\AuthBundle\Resources\contao\models\AuthMemberModel;
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

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $loginUsername = $event->getAuthenticationToken()->getUsername();

        if (AuthUserModel::findByUsername($loginUsername)->con4gisAuthUser == '1' || AuthMemberModel::findByUsername($loginUsername)->con4gisAuthMember == '1') {
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
            $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
            $authSettings = $authSettingsRepo->findAll();

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

            $ldapConnection = new LdapConnection();
        }

        if (TL_MODE == 'BE') {
            $beUser = AuthUserModel::findByUsername($loginUsername);

            if ($beUser && $beUser->con4gisAuthUser == '1') {

                //Get LDAP Admin Group
                $authBeGroupsRepo = $em->getRepository(Con4gisAuthBackendGroups::class);
                $authBeGroups = $authBeGroupsRepo->findAll();

                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
                $authSettings = $authSettingsRepo->findAll();

                $adminGroup = $authBeGroups[0]->getAdminGroup();

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $authSettings);

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

                if ($beUser->con4gisAuthUser == '0') {
                    $beUser->con4gisAuthUser = '1';
                }

                $user = AuthUserModel::findByUsername($loginUsername);
                if ($user) {
                    foreach ($groups as $group) {
                        if ($group == $adminGroup) {
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
            $feUser = AuthMemberModel::findByUsername($loginUsername);

            if ($feUser && $feUser->con4gisAuthMember == '1') {

                $authSettingsRepo = $em->getRepository(Con4gisAuthSettings::class);
                $authSettings = $authSettingsRepo->findAll();

                $groups = $ldapConnection->getLdapUserGroups($loginUsername, $authSettings);

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

                if ($feUser->con4gisAuthMember == '0') {
                    $feUser->con4gisAuthMember = '1';
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

                $feUser->con4gisAuthMember = '1';
            }
        }
    }

    public function generatePassword() {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';
        return hash('sha384', substr(str_shuffle($chars), 0, 18));
    }
}