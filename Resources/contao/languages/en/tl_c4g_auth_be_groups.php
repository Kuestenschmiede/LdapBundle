<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindDn'] = array('Bind-DN', 'User that is used for the search. (Example: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['baseDn'] = array('Base-DN', 'Starting point for the search in LDAP. (Example: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['password'] = array('Password', 'Enter the password of the Bind-DN user here');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['filter'] = array('Filter', 'This can be used to filter which groups should be available for import. (example: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['encryption'] = array('Encryption', 'Specify the type of encryption here.');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['server'] = array('Server address', 'IP or address of the LDAP servers');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['port'] = array('Port', 'Standard port 389');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['groups'] = array('Available groups');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['adminGroup'] = array('Admin group', 'Select the group for which an admin access is to be created. This group does NOT need to be imported.');

$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['infotext'] = 'The groups are updated each time these settings are saved. The groups are assigned to the users at each login.';

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['plain'] = 'No encryption';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ssl'] = 'SSL';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindError'] = 'No connection to the LDAP server could be established.';