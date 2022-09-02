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

use Contao\System;

$GLOBALS['BE_MOD']['con4gis'] = key_exists('con4gis', $GLOBALS['BE_MOD']) && $GLOBALS['BE_MOD']['con4gis'] ? $GLOBALS['BE_MOD']['con4gis'] : [];

$GLOBALS['BE_MOD']['con4gis'] = array_merge($GLOBALS['BE_MOD']['con4gis'], [
    'C4gLdapSettings' => array
    (
        'brick' => 'ldap',
        'tables'    => array('tl_c4g_ldap_settings'),
        'icon'      => 'bundles/con4giscore/images/be-icons/edit.svg'
    ),
    'C4gLdapBeGroups' => array
    (
        'brick' => 'ldap',
        'tables'    => array('tl_c4g_ldap_be_groups'),
        'icon'      => 'bundles/con4gisldap/images/be-icons/con4gis_ldap_user.svg'
    ),
    'C4gLdapFeGroups' => array
    (
        'brick' => 'ldap',
        'tables'    => array('tl_c4g_ldap_fe_groups'),
        'icon'      => 'bundles/con4gisldap/images/be-icons/con4gis_ldap_member.svg'
    )
]);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['importUser'][] = array('con4gis\LdapBundle\Classes\LoginNewUser', 'importUserBeforeAuthenticate');
$GLOBALS['TL_HOOKS']['createNewUser'][] = array('con4gis\LdapBundle\Classes\LdapAccount', 'onAccountCreation');
$GLOBALS['TL_HOOKS']['activateAccount'][] = array('con4gis\LdapBundle\Classes\LdapAccount', 'onAccountActivation');
$GLOBALS['TL_HOOKS']['updatePersonalData'][] = array('con4gis\LdapBundle\Classes\MemberAccountData', 'updateMemberData');
$GLOBALS['TL_HOOKS']['setNewPassword'][] = array('con4gis\LdapBundle\Classes\MemberAccountData', 'updateMemberPassword');

/**
 * Crons
 */
$GLOBALS['TL_CRON']['minutely'][] = [\con4gis\LdapBundle\Classes\Cron\SyncLdapDataCron::class, 'onMinutely'];