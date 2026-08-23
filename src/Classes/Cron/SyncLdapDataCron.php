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
namespace con4gis\LdapBundle\Classes\Cron;

use con4gis\LdapBundle\Entity\Con4gisLdapFrontendGroups;
use con4gis\LdapBundle\Entity\Con4gisLdapSettings;
use con4gis\LdapBundle\Resources\contao\models\LdapMemberModel;
use Contao\Database;
use Contao\MemberGroupModel;
use Contao\MemberModel;
use Contao\System;
use con4gis\LdapBundle\Classes\LdapConnection;
use Psr\Log\LogLevel;
use Contao\CoreBundle\Monolog\ContaoContext;

class SyncLdapDataCron
{
    public function onMinutely()
    {
        $db = Database::getInstance();
        if (!$db->tableExists('tl_c4g_ldap_settings')) {
            return;
        }
        $ldapSettingsData = $db->prepare('SELECT * FROM tl_c4g_ldap_settings')->execute()->fetchAssoc();

        if ($ldapSettingsData && isset($ldapSettingsData['updateData']) && $ldapSettingsData['updateData'] == 1) {
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');

            $ldapConnection = new LdapConnection();

            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapSettings = $ldapSettingsRepo->findAll();
            if (empty($ldapSettings)) {
                return;
            }

            $baseDn = $ldapSettings[0]->getBaseDn();
            $bindDn = $ldapSettings[0]->getBindDn();
            $bindPassword = $ldapSettings[0]->getPassword();
            $encryption = $ldapSettings[0]->getEncryption();
            $server = $ldapSettings[0]->getServer();
            $port = $ldapSettings[0]->getPort();
            $userFilter = $ldapSettings[0]->getUserFilter();
            $updateFilter = $ldapSettings[0]->getUpdateFilter() ?: '(|(cn=*)(uid=*))';
            $ldapUsernameField = strtolower($userFilter ?: 'uid');
            $ldapEmailField = strtolower($ldapSettings[0]->getEmail() ?: 'mail');
            $ldapFirstnameField = strtolower($ldapSettings[0]->getFirstname() ?: 'givenname');
            $ldapLastnameField = strtolower($ldapSettings[0]->getLastname() ?: 'sn');

            $adServer = '';
            if ($server && $port) {
                if ($encryption == 'ssl') {
                    $adServer = 'ldaps://' . $server . ':' . $port;
                } else {
                    $adServer = 'ldap://' . $server . ':' . $port;
                }
            }

            if (!$adServer) {
                return;
            }

            $ldapUsers = $ldapConnection->filterLdap($bindDn, $bindPassword, $updateFilter, $baseDn, $adServer);
            if (!$ldapUsers) {
                return;
            }

            array_shift($ldapUsers);

            $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
            $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();
            $mappingDatas = !empty($ldapFrontendGroups) ? $ldapFrontendGroups[0]->getFieldMapping() : [];

            foreach ($ldapUsers as $ldapUser) {
                $username = isset($ldapUser[$ldapUsernameField][0]) ? $ldapUser[$ldapUsernameField][0] : '';
                if (!$username) {
                    continue;
                }

                $firstname = (!empty($ldapFirstnameField) && isset($ldapUser[$ldapFirstnameField][0])) ? $ldapUser[$ldapFirstnameField][0] : '';
                $lastname = (!empty($ldapLastnameField) && isset($ldapUser[$ldapLastnameField][0])) ? $ldapUser[$ldapLastnameField][0] : '';
                $email = (!empty($ldapEmailField) && isset($ldapUser[$ldapEmailField][0])) ? $ldapUser[$ldapEmailField][0] : '';

                $member = LdapMemberModel::findByUsername($username);
                if (!$member) {
                    $member = new LdapMemberModel();
                    $member->username = $username;
                    $member->dateAdded = time();
                    $member->con4gisLdapMember = '1';
                    $member->login = '1';
                    $member->password = $this->generatePassword();
                    $member->save();
                    $member = LdapMemberModel::findByUsername($username);
                }

                if (!$member) {
                    continue;
                }

                $member->firstname = $firstname;
                $member->lastname = $lastname;
                $member->email = $email;
                $member->con4gisLdapMember = '1';

                $groups = $ldapConnection->getLdapUserGroups($username, $ldapSettings);
                $contaoGroups = [];
                foreach ($groups as $group) {
                    if ($foundGroup = MemberGroupModel::findByName($group)) {
                        $contaoGroups[] = $foundGroup->id;
                    }
                }

                if (!empty($contaoGroups)) {
                    $member->groups = serialize($contaoGroups);
                }

                if (!empty($mappingDatas)) {
                    foreach ($mappingDatas as $mappingData) {
                        $contaoField = $mappingData['contaoField'];
                        $ldapField = strtolower($mappingData['ldapField']);
                        $ldapFieldData = isset($ldapUser[$ldapField][0]) ? $ldapUser[$ldapField][0] : '';
                        if ($contaoField == 'country') {
                            $ldapFieldData = strtolower($ldapFieldData);
                        }
                        if ($contaoField) {
                            $member->$contaoField = $ldapFieldData;
                        }
                    }
                }

                $member->tstamp = time();

                try {
                    $member->save();
                } catch (\Exception $e) {
                    System::getContainer()
                        ->get('monolog.logger.contao')
                        ->log(LogLevel::ERROR, 'Fehler beim automatischen Abgleich der Mitglieder über LDAP: '.$e->getMessage(), array(
                            'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, ContaoContext::CRON
                            )));
                    break;
                }
            }
        }
    }

    public function generatePassword()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';

        return hash('sha384', substr(str_shuffle($chars), 0, 18));
    }
}
