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

use con4gis\AuthBundle\Entity\Con4gisLdapFrontendGroups;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use con4gis\AuthBundle\Resources\contao\models\LdapMemberModel;
use con4gis\AuthBundle\Resources\contao\models\LdapUserModel;
use Contao\Database;
use Contao\System;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use con4gis\AuthBundle\Classes\LdapConnection;
use con4gis\AuthBundle\Entity\Con4gisLdapSettings;

class LoginNewUser implements ServiceAnnotationInterface
{

    /**
     * @Hook("importUser")
     */
    public function importUserBeforeAuthenticate(string $username, string $password, string $table): bool
    {

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $authSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $authSettings = $authSettingsRepo->findAll();

        $encryption = $authSettings[0]->getEncryption();
        $server = $authSettings[0]->getServer();
        $port = $authSettings[0]->getPort();
        $bindDn = $authSettings[0]->getBindDn();
        $baseDn = $authSettings[0]->getBaseDn();
        $bindPassword = $authSettings[0]->getPassword();
        $userFilter = '(&(' . $authSettings[0]->getUserFilter() . '=' . $username . '))';

        if ($encryption == 'ssl') {
            $adServer = 'ldaps://' . $server . ':' . $port;
        } elseif ($encryption == 'plain' || $encryption == 'tls') {
            $adServer = 'ldap://' . $server . ':' . $port;
        }

        $ldapConnection = new LdapConnection();
        $ldapUser = $ldapConnection->filterLdap($bindDn, $bindPassword, $userFilter, $baseDn, $adServer);

        if ($ldapUser['count'] != 0) {
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';
            $password = hash('sha384', substr(str_shuffle($chars), 0, 18));

            if ('tl_user' === $table) {
                // Import user from an LDAP server
                if (LdapUserModel::findByUsername($username)) {
                    return true;
                }
                $user = new LdapUserModel();
                $user->con4gisLdapUser = 1;
            } elseif ('tl_member' === $table) {
                // Import user from an LDAP server
                if (LdapMemberModel::findByUsername($username)) {
                    return true;
                }
                $user = new LdapMemberModel();
                $user->login = '1';
                $user->con4gisLdapMember = 1;
            } else {
                return false;
            }

            $user->username = $username;
            $user->password = $password;
            $user->dateAdded = time();
            $user->save();

            return true;
        } else {
            return false;
        }
    }
}
