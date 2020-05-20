<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['ldap'] = 'LDAP settings';
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['filter'] = array('Filter', 'This can be used to filter which groups should be available for import. (example: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['groups'] = array('Available groups');

$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['infotext'] = 'The groups are updated each time these settings are saved. The groups are assigned to the users at each login. If the filter is changed, groups that no longer exist are automatically deleted.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['bindError'] = 'No connection to the LDAP server could be established.';