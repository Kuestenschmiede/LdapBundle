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
use Contao\Message;
use Contao\System;
use Contao\UserGroupModel;
use con4gis\LdapBundle\Classes\LdapConnection;

/**
 * Table tl_c4g_ldap_settings
 */
$GLOBALS['TL_DCA']['tl_c4g_ldap_settings'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => false,
        'notDeletable'                => true,
        'notCopyable'                 => true,
        'onload_callback'			  => array
        (
            array('tl_c4g_ldap_settings', 'loadDataset'),
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
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['edit'],
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
        '__selector__'                => array('updateData','groupFilterCheck'),
        'default'                     => '{ldap}, serverType, server, port, encryption, baseDn, bindDn, password, userFilter, email, firstname, lastname, updateData, groupFilterCheck, linkWithUserMail'
    ),

    'subpalettes' => array
    (
        'updateData'                                 => 'updateFilter',
        'groupFilterCheck'                           => 'groupFilter'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['id'],
            'sorting'                 => true,
            'search'                  => true,
        ),

        'tstamp' => array(
            'default'                 => 0,
        ),

        'serverType' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['serverType'],
            'exclude'                 => true,
            'filter'                  => false,
            'inputType'               => 'select',
            'options'                 => [
                'windows_ad'               => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['windows_ad'],
                'openldap'                 => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['openldap'],
            ],
            'default'                 => 'windows_ad',
        ),

        'bindDn' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindDn'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => ['mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'long'],
        ),

        'baseDn' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['baseDn'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => ['mandatory' => true, 'decodeEntities' => true],
        ),

        'password' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['password'],
            'default'                 => '',
            'exclude'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'long',],
        ),

        'encryption' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['encryption'],
            'exclude'                 => true,
            'filter'                  => false,
            'inputType'               => 'select',
            'options'                 => [
                'plain'               => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['plain'],
//                'ssl'                 => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['ssl'],
                'tls'                 => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['tls'],
            ],
            'default'                 => 'plain',
            'eval'                    => ['submitOnChange' => false],
        ),

        'server' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['server'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'long'),
        ),

        'port' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['port'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('mandatory' => true, 'decodeEntities' => true, 'tl_class' => 'long'),
        ),

        'email' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['email'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('mandatory' => true, 'decodeEntities' => true,),
        ),

        'firstname' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['firstname'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => ['decodeEntities' => true, 'tl_class' => 'long'],
        ),

        'lastname' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['lastname'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => ['decodeEntities' => true, 'tl_class' => 'long'],
        ),

        'userFilter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['userFilter'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('decodeEntities' => true, 'mandatory' => true),
        ),

        'updateData' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateData'],
            'exclude'                 => true,
            'default'                 => false,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'clr', 'submitOnChange' => true],
        ),

        'updateFilter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateFilter'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '(&(objectClass=person))',
            'eval'                    => array('decodeEntities' => true, 'mandatory' => true),
        ),

        'groupFilterCheck' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['groupFilterCheck'],
            'exclude'                 => true,
            'default'                 => false,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class'=>'clr', 'submitOnChange' => true],
        ),

        'groupFilter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['groupFilter'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '(&(objectClass=groupOfNames))',
            'eval'                    => array('decodeEntities' => true, 'mandatory' => true),
        ),

        'linkWithUserMail' => array(
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_ldap_be_groups']['linkWithUserMail'],
            'exclude'                 => true,
            'filter'                  => false,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => ['submitOnChange' => false, 'tl_class'=>'clr'],
        ),

    ),
);
class tl_c4g_ldap_settings extends \Backend
{
    public function loadDataset(Contao\DataContainer $dc)
    {
        $objConfig = Database::getInstance()->prepare("SELECT id FROM tl_c4g_ldap_settings")->execute();

        if (\Input::get('key')) return;

        if(!$objConfig->numRows && !\Input::get('act'))
        {
            $this->redirect($this->addToUrl('act=create'));
        }


        if(!\Input::get('id') && !\Input::get('act'))
        {
            $GLOBALS['TL_DCA']['tl_c4g_settings']['config']['notCreatable'] = true;
            $this->redirect($this->addToUrl('act=edit&id='.$objConfig->id));
        }

        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['infotext']);

        $ldapConnection = new LdapConnection();

        $ldap = $ldapConnection->ldapConnect();

        $em = System::getContainer()->get('doctrine.orm.default_entity_manager');
        $ldapSettingsRepo = $em->getRepository(Con4gisLdapSettings::class);
        $ldapSettings = $ldapSettingsRepo->findAll();

        if ($ldapSettings && count($ldapSettings) > 0) {
            $encryption = $ldapSettings[0]->getEncryption();
            $bindDn = $ldapSettings[0]->getBindDn();
            $bindPassword = $ldapSettings[0]->getPassword();
            $server = $ldapSettings[0]->getServer();
            $port = $ldapSettings[0]->getPort();
            $baseDn = $ldapSettings[0]->getBaseDn();
        }

        if(!$ldap) {
            Message::addError($GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindError']);
        }

        if (!$ldapConnection->ldapBind($ldap) && !$baseDn && !$bindDn && !$password && !$server && !$port) {
            Message::addError($GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindError']);
        }

    }
}
