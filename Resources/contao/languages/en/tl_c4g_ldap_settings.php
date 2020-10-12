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
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindDn'] = array('Bind-DN', 'User that is used for the search. (Example: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['baseDn'] = array('Base-DN', 'Starting point for the search in LDAP. (Example: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['password'] = array('Password', 'Enter the password of the Bind-DN user here');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['encryption'] = array('Encryption', 'Specify the type of encryption here.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['server'] = array('Server address', 'IP or address of the LDAP servers');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['port'] = array('Port', 'Standard port 389');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['email'] = array('E-Mail', 'Enter the field from the LDAP server that contains the e-mail address.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['firstname'] = array('Firstname', 'Enter the field from the LDAP server that contains the firstname.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['lastname'] = array('Lastname', 'Enter the field from the LDAP server that contains the lastname.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['userFilter'] = array('User filter', 'Name the attribute that contains the user name here. (Example: uid oder sAMAccountName)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateData'] = array('Automatically update data from LDAP', 'Activate this checkbox if the LDAP data should be updated every minute via Contao Cron.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateFilter'] = array('Updatefilter', 'Here you can filter which members from the LDAP should be imported and updated regularly. (Example: (&(objectClass=user)) )');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['linkWithUserMail'] = array('Link with existing user', 'If a user with the same email address already exists, the LDAP user is linked to it');

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['plain'] = 'No encryption';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['ssl'] = 'SSL';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['tls'] = 'TLS';

$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['infotext'] = 'The groups are updated each time these settings are saved. The groups are assigned to the users at each login. If the filter is changed, groups that no longer exist are automatically deleted.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindError'] = 'No connection to the LDAP server could be established.';