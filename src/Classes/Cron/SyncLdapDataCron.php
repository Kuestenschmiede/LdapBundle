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
        $ldapSettings = $db->prepare('SELECT * FROM tl_c4g_ldap_settings')->execute()->fetchAssoc();

        if ($ldapSettings['updateData'] == 1) {
            $em = System::getContainer()->get('doctrine.orm.default_entity_manager');

            $ldapConnection = new LdapConnection();

            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
            $ldapSettings = $ldapSettingsRepo->findAll();

            $baseDn = $ldapSettings[0]->getBaseDn();
            $bindDn = $ldapSettings[0]->getBindDn();
            $bindPassword = $ldapSettings[0]->getPassword();
            $encryption = $ldapSettings[0]->getEncryption();
            $server = $ldapSettings[0]->getServer();
            $port = $ldapSettings[0]->getPort();
            $updateFilter = '(|(cn=*)(uid=*))';
            $ldapUsernames = [];
            if ($server && $port && $encryption) {
                if ($encryption == 'ssl') {
                    $adServer = 'ldaps://' . $server . ':' . $port;
                } elseif ($encryption == 'plain' || $encryption == 'tls') {
                    $adServer = 'ldap://' . $server . ':' . $port;
                }
            }

            if ($adServer) {
                $ldapUsers = $ldapConnection->filterLdap($bindDn, $bindPassword, $updateFilter, $baseDn, $adServer);
                if ($ldapUsers) {
                    array_shift($ldapUsers);
                    $ldapUsernameField = strtolower($ldapSettings[0]->getUserFilter());

                    foreach ($ldapUsers as $ldapUser) {
                        $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
                        $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();
                        $mappingDatas = $ldapFrontendGroups[0]->getFieldMapping();
                        $username = $ldapUser[$ldapUsernameField][0];
                        if ($username) {
                            $ldapUsernames[] = $username;
                        }
                    }
                }
            }

            $ldapConnection = new LdapConnection();

            $ldap = $ldapConnection->ldapConnect();
            $bind = $ldapConnection->ldapBind($ldap);

            if ($ldapSettings[0]->getUpdateFilter() != '') {
                $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
                $ldapSettings = $ldapSettingsRepo->findAll();

                $baseDn = $ldapSettings[0]->getBaseDn();
                $bindDn = $ldapSettings[0]->getBindDn();
                $bindPassword = $ldapSettings[0]->getPassword();
                $encryption = $ldapSettings[0]->getEncryption();
                $server = $ldapSettings[0]->getServer();
                $port = $ldapSettings[0]->getPort();
                $updateFilter = $ldapSettings[0]->getUpdateFilter();
                if ($server && $port && $encryption) {
                    if ($encryption == 'ssl') {
                        $adServer = 'ldaps://' . $server . ':' . $port;
                    } elseif ($encryption == 'plain' || $encryption == 'tls') {
                        $adServer = 'ldap://' . $server . ':' . $port;
                    }
                }
                if ($adServer) {
                    $ldapUsers = $ldapConnection->filterLdap($bindDn, $bindPassword, $updateFilter, $baseDn, $adServer);

                    if ($ldapUsers) {
                        array_shift($ldapUsers);
                        $ldapUsernameField = strtolower($ldapSettings[0]->getUserFilter());
                        $ldapEmailField = strtolower($ldapSettings[0]->getEmail());
                        $ldapFirstnameField = strtolower($ldapSettings[0]->getFirstname());
                        $ldapLastnameField = strtolower($ldapSettings[0]->getLastname());
                        $ldapUsernames = [];
                        foreach ($ldapUsers as $ldapUser) {
                            $ldapFrontendGroupsRepo = $em->getRepository(Con4gisLdapFrontendGroups::class);
                            $ldapFrontendGroups = $ldapFrontendGroupsRepo->findAll();
                            $mappingDatas = $ldapFrontendGroups[0]->getFieldMapping();
                            $username = $ldapUser[$ldapUsernameField][0];
                            $ldapUsernames[] = $username;
                            $firstname = $ldapUser[$ldapFirstnameField][0];
                            $lastname = $ldapUser[$ldapLastnameField][0];
                            $email = $ldapUser[$ldapEmailField][0];
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
                                $contaoGroups = serialize($contaoGroups);
                                $member->groups = $contaoGroups;
                                $member->tstamp = time();
                            }

                            //ToDo: Individuellen Felder aus dem MultiColumnWizard verknüpfen
                            foreach ($mappingDatas as $mappingData) {
                                $contaoField = $mappingData['contaoField'];
                                $ldapField = strtolower($mappingData['ldapField']);
                                $ldapFieldData = $ldapUser[$ldapField][0];
                                if ($contaoField == 'country') {
                                    $ldapFieldData = strtolower($ldapFieldData);
                                }
                                if (!$ldapFieldData) {
                                    $ldapFieldData = '';
                                }
                                $member->$contaoField = $ldapFieldData;
                            }
                            try {
                                $member->save();
                            } catch (\Exception $e) {
                                \System::getContainer()
                                    ->get('monolog.logger.contao')
                                    ->log(LogLevel::ERROR, 'Fehler beim automatischen Abgleich der Mitglieder über LDAP: '.$e, array(
                                        'contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON
                                        )));
                                break;
                            }
                        }

                        //Delete old con4gis LDAP member
                        $allMember = MemberModel::findBy('con4gisLdapMember', '1');
                        if ($allMember) {
                            foreach ($allMember as $oneMember) {
                                if (!in_array($oneMember->username, $ldapUsernames)) {
                                    $oneMember->delete();
                                }
                            }
                        }
                    }

                    $test = 'test';
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
