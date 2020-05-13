<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindDn'] = array('Bind-DN', 'Benutzer der für die Suche genutzt wird. (Beispiel: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['baseDn'] = array('Base-DN', 'Startpunkt für die Suche im LDAP. (Beispiel: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['password'] = array('Passwort', 'Hier das Password des Bind-DN Nutzers eingeben');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['filter'] = array('Filter', 'Hiermit kann gefilter werden, welche Gruppen zum Import bereitstehen sollen. (Beispiel: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['encryption'] = array('Verschlüsselung', 'Lege hier die Art der Verschlüsselung fest.');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['server'] = array('Serveradresse', 'IP oder Adresse des LDAP-Servers');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['port'] = array('Port', 'Standardport 389');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['groups'] = array('Verfügbare Gruppen');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['adminGroup'] = array('Administratoren-Gruppe', 'Hier die Gruppe auswählen, für die ein Admin-Zugang angelegt wird. Diese Gruppe muss NICHT importiert werden.');

$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert.';

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['plain'] = 'Ohne Verschlüsselung';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ssl'] = 'SSL';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';