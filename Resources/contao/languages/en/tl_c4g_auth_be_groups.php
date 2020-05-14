<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['filter'] = array('Filter', 'This can be used to filter which groups should be available for import. (example: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['groups'] = array('Available groups');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['adminGroup'] = array('Admin group', 'Select the group for which an admin access is to be created. This group does NOT need to be imported.');

$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['infotext'] = 'The groups are updated each time these settings are saved. The groups are assigned to the users at each login.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindError'] = 'No connection to the LDAP server could be established.';