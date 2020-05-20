<?php

/** Field Labels */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['filter'] = array('Filter', 'Hiermit kann gefilter werden, welche Gruppen zum Import bereitstehen sollen. (Beispiel: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['groups'] = array('Verfügbare Gruppen');
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['adminGroup'] = array('Administratoren-Gruppe', 'Hier die Gruppe auswählen, für die ein Admin-Zugang angelegt wird. Diese Gruppe muss NICHT importiert werden.');

$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert. Die Zuordnung der Gruppen zu den Usern erfolgt bei jedem Login. Falls der Filter geändert wird, werden dann nicht mehr vorhandene Gruppen automatisch gelöscht.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_be_groups']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';