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

use con4gis\LdapBundle\Entity\Con4gisLdapSettings;
use Contao\Backend;
use Contao\Database;
use Contao\DC_Table;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\UserGroupModel;
use con4gis\LdapBundle\Classes\LdapConnection;

/**
 * Table tl_c4g_ldap_be_groups
 */
$GLOBALS['TL_DCA']['tl_c4g_ldap_be_groups'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => DC_Table::class,
        'enableVersioning'            => false,
        'notDeletable'                => true,
        'notCopyable'                 => true,
        'onload_callback'			  => array
        (
            array('tl_c4g_ldap_be_groups', 'loadDataset'),
        ),
        'onsubmit_callback'           => array
        (
            array('tl_c4g_ldap_be_groups', 'saveDataset'),
        ),
    ),
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('id'),
            'panelLayout'             => 'filter;sort,search,limit',
            'headerFields'            => array('bindDn', 'baseDn', 'password', 'filter'),
        ),
        'label' => array
        (
            'fields'                  => array('bindDn', 'baseDn', 'password', 'filter'),
            'showColumns'             => true,
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg',
            )
        )
    ),

    // Select
    'select' => array
    (
        'buttons_callback' => array()
    ),

    // Edit
    'edit' => array
    (
        'buttons_callback' => array()
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array(''),
        'default'                     => '{ldap}, filter, adminGroup, groups'
    ),

    'subpalettes' => array
    (
        ''                                 => ''
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['id'],
            'sorting'                 => true,
            'search'                  => true,
        ),

        'tstamp' => array(
            'default'                 => 0,
        ),

        'filter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['filter'],
            'sorting'                 => true,
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('decodeEntities' => true),
        ),

        'groups' => array(

            'label'            => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['groups'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkboxWizard',
            'default'          => '',
            'eval'             => ['maxlength' => 360, 'multiple' => true, 'tl_class' => 'long clr'],
            'options_callback' => array('tl_c4g_ldap_be_groups', 'groupsCallback'),

        ),
        'adminGroup' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['adminGroup'],
            'exclude'                 => true,
            'filter'                  => false,
            'inputType'               => 'select',
            'default'                 => [],
            'load_callback'           => [['tl_c4g_ldap_be_groups', 'checkForSingleAdminGroup']],
            'eval'                    => ['submitOnChange' => false, 'includeBlankOption' => true, 'multiple' => true, 'chosen' => true],
            'options_callback'        => ['tl_c4g_ldap_be_groups', 'groupsCallback'],
        ),
    ),
);
class tl_c4g_ldap_be_groups extends \Contao\Backend
{
    public function loadDataset(?\Contao\DataContainer $dc = null)
    {
        $objConfig = Database::getInstance()->prepare("SELECT id FROM tl_c4g_ldap_be_groups")->execute();

        if (\Contao\Input::get('key')) return;

        if (!$objConfig->numRows && !\Contao\Input::get('act'))
        {
            $this->redirect($this->addToUrl('act=create'));
        }

        if (!\Contao\Input::get('id') && !\Contao\Input::get('act'))
        {
            $GLOBALS['TL_DCA']['tl_c4g_ldap_be_groups']['config']['notCreatable'] = true;
            $this->redirect($this->addToUrl('act=edit&id='.$objConfig->id));
        }

        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['infotext']);

        $ldapConnection = new LdapConnection();

        $ldap = $ldapConnection->ldapConnect();

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();

        $bindDn = '';
        $bindPassword = '';
        $server = '';
        $port = '';
        $baseDn = '';

        if ($ldapSettings && count($ldapSettings) > 0) {
            $encryption = $ldapSettings[0]->getEncryption();
            $bindDn = $ldapSettings[0]->getBindDn();
            $bindPassword = $ldapSettings[0]->getPassword();
            $server = $ldapSettings[0]->getServer();
            $port = $ldapSettings[0]->getPort();
            $baseDn = $ldapSettings[0]->getBaseDn();
        }

        if (!$ldap) {
            \Contao\Message::addError($GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['bindError']);
        }

        if ($ldap && !$ldapConnection->ldapBind($ldap) && !$bindDn && !$bindPassword && !$server && !$port) {
            \Contao\Message::addError($GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['bindError']);
        }
    }

    public function saveDataset(\Contao\DataContainer $dc) {
        $db = Database::getInstance();
        $rawGroups = $dc->activeRecord ? $dc->activeRecord->groups : null;
        $groups = is_array($rawGroups) ? $rawGroups : \Contao\StringUtil::deserialize($rawGroups, true);

        $currentTime = time();

        if (is_array($groups)) {
            foreach ($groups as $group) {
                $contaoGroup = UserGroupModel::findOneByName($group);
                if (!$contaoGroup) {
                    $db->prepare("INSERT INTO tl_user_group (tstamp, name, con4gisLdapUserGroup) VALUES (?, ?, 1)")->execute($currentTime, $group);
                }
            }

            $currentGroups = $db->prepare("SELECT name FROM tl_user_group WHERE con4gisLdapUserGroup=1;")->execute();
            $currentGroups = $currentGroups->fetchAllAssoc();

            $ldapConnection = new LdapConnection();
            $ldap = $ldapConnection->ldapConnect();

            if ($ldap && $ldapConnection->ldapBind($ldap)) {
                foreach ($currentGroups as $currentGroup) {
                    if (!in_array($currentGroup['name'], $groups)) {
                        $db->prepare("DELETE FROM tl_user_group WHERE name=? AND con4gisLdapUserGroup=1")->execute($currentGroup['name']);
                    }
                }
            }
        }
    }

    public function groupsCallback(?\Contao\DataContainer $dc = null) {
        $db = Database::getInstance();
        $ldapSettingsResult = $db->prepare("SELECT * FROM tl_c4g_ldap_settings")->execute()->fetchAllAssoc();
        if (empty($ldapSettingsResult)) {
            return [];
        }
        $ldapSettings = $ldapSettingsResult[0];

        $baseDn = $ldapSettings['baseDn'] ?? '';
        $filter = ($dc && $dc->activeRecord) ? $dc->activeRecord->filter : '';

        $groups = [];

        $ldapConnection = new LdapConnection();
        $ldap = $ldapConnection->ldapConnect();
        $bind = false;
        
        if ($ldap) {
            $bind = $ldapConnection->ldapBind($ldap);
        }

        if ($bind && $baseDn) {
            if (!$filter) {
                $filter = "(|(cn=*)(uid=*))";
            }
            $result = @ldap_search($ldap, $baseDn, $filter);
            $ldapGroups = $ldap && $result ? ldap_get_entries($ldap, $result) : false;
            if ($ldapGroups) {
                array_shift($ldapGroups);

                foreach ($ldapGroups as $ldapGroup) {
                    if (isset($ldapGroup['dn'])) {
                        $group = strstr($ldapGroup['dn'], ',', true);
                        $group = trim(substr($group, strpos($group, '=') + 1));
                        $groups[$group] = $group;
                    }
                }
            }
        }

        return $groups;
    }
    
    public function checkForSingleAdminGroup($value)
    {
        if (is_int($value)) {
            $value = [$value];
        }
        
        return $value;
    }
}