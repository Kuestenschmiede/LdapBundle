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
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['filter'] = array('Filter', 'Hiermit kann gefilter werden, welche Gruppen zum Import bereitstehen sollen. (Beispiel: (&(objectClass=group)) )');
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['groups'] = array('Verfügbare Gruppen');
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['adminGroup'] = array('Administratoren-Gruppe', 'Hier die Gruppe auswählen, für die ein Admin-Zugang angelegt wird. Diese Gruppe muss NICHT importiert werden.');

$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert. Die Zuordnung der Gruppen zu den Usern erfolgt bei jedem Login. Falls der Filter geändert wird, werden dann nicht mehr vorhandene Gruppen automatisch gelöscht.';

/** Error Messages */
$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';