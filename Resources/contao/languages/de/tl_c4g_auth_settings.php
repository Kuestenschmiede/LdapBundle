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
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['bindDn'] = array('Bind-DN', 'Benutzer der für die Suche genutzt wird. (Beispiel: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['baseDn'] = array('Base-DN', 'Startpunkt für die Suche im LDAP. (Beispiel: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['password'] = array('Passwort', 'Hier das Password des Bind-DN Nutzers eingeben');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['encryption'] = array('Verschlüsselung', 'Lege hier die Art der Verschlüsselung fest.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['server'] = array('Serveradresse', 'IP oder Adresse des LDAP-Servers');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['port'] = array('Port', 'Standardport 389');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['email'] = array('E-Mail', 'Hier das Feld vom LDAP-Server eintragen, welches die E-Mail Adresse enthält.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['firstname'] = array('Vorame', 'Hier das Feld vom LDAP-Server eintragen, welches den Vornamen enthält.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['lastname'] = array('Nachname', 'Hier das Feld vom LDAP-Server eintragen, welches den Nachnamen enthält.');
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['userFilter'] = array('User-Filter', 'Hier das Attribut benennen, welches den Benutzernamen enthält. (Beispiel: uid oder sAMAccountName)');

/** Options */
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['plain'] = 'Ohne Verschlüsselung';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['ssl'] = 'SSL';

$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';
$GLOBALS['TL_LANG']['tl_c4g_auth_settings']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert. Die Zuordnung der Gruppen zu den Usern erfolgt bei jedem Login. Falls der Filter geändert wird, werden dann nicht mehr vorhandene Gruppen automatisch gelöscht.';