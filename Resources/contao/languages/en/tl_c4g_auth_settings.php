<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['bindDn'] = array('Bind-DN', 'User that is used for the search. (Example: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['baseDn'] = array('Base-DN', 'Starting point for the search in LDAP. (Example: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['password'] = array('Password', 'Enter the password of the Bind-DN user here');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['encryption'] = array('Encryption', 'Specify the type of encryption here.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['server'] = array('Server address', 'IP or address of the LDAP servers');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['port'] = array('Port', 'Standard port 389');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['email'] = array('E-Mail', 'Enter the field from the LDAP server that contains the e-mail address.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['name'] = array('Name', 'Enter the field from the LDAP server that contains the name.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['userFilter'] = array('User filter', 'Name the attribute that contains the user name here. (Example: uid oder sAMAccountName)');

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['plain'] = 'No encryption';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ssl'] = 'SSL';