<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['filter'] = array('Filter', 'Hiermit kann gefilter werden, welche Gruppen zum Import bereitstehen sollen. (Beispiel: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['groups'] = array('Verfügbare Gruppen');
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['adminGroup'] = array('Administratoren-Gruppe', 'Hier die Gruppe auswählen, für die ein Admin-Zugang angelegt wird. Diese Gruppe muss NICHT importiert werden.');

$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert. Die Zuordnung der Gruppen zu den Usern erfolgt bei jedem Login.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';