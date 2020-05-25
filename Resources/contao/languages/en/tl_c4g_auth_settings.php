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
/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['bindDn'] = array('Bind-DN', 'User that is used for the search. (Example: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['baseDn'] = array('Base-DN', 'Starting point for the search in LDAP. (Example: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['password'] = array('Password', 'Enter the password of the Bind-DN user here');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['encryption'] = array('Encryption', 'Specify the type of encryption here.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['server'] = array('Server address', 'IP or address of the LDAP servers');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['port'] = array('Port', 'Standard port 389');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['email'] = array('E-Mail', 'Enter the field from the LDAP server that contains the e-mail address.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['firstname'] = array('Firstname', 'Enter the field from the LDAP server that contains the firstname.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['lastname'] = array('Lastname', 'Enter the field from the LDAP server that contains the lastname.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['userFilter'] = array('User filter', 'Name the attribute that contains the user name here. (Example: uid oder sAMAccountName)');

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['plain'] = 'No encryption';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ssl'] = 'SSL';

$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['infotext'] = 'The groups are updated each time these settings are saved. The groups are assigned to the users at each login. If the filter is changed, groups that no longer exist are automatically deleted.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['bindError'] = 'No connection to the LDAP server could be established.';