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
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['ldap'] = 'LDAP Einstellungen';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindDn'] = array('Bind-DN', 'Benutzer der für die Suche genutzt wird. (Beispiel: cn=read-only-admin,cn=Users,dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['baseDn'] = array('Base-DN', 'Startpunkt für die Suche im LDAP. (Beispiel: dc=ad,dc=example,dc=com)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['password'] = array('Passwort', 'Hier das Password des Bind-DN Nutzers eingeben');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['encryption'] = array('Verschlüsselung', 'Lege hier die Art der Verschlüsselung fest.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['serverType'] = array('LDAP-Server', 'Lege hier die Art des LDAP-Servers fest.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['server'] = array('Serveradresse', 'IP oder Adresse des LDAP-Servers');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['port'] = array('Port', 'Standardport 389');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['email'] = array('E-Mail', 'Hier das Feld vom LDAP-Server eintragen, welches die E-Mail Adresse enthält.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['firstname'] = array('Vorame', 'Hier das Feld vom LDAP-Server eintragen, welches den Vornamen enthält.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['lastname'] = array('Nachname', 'Hier das Feld vom LDAP-Server eintragen, welches den Nachnamen enthält.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['userFilter'] = array('User-Filter', 'Hier das Attribut benennen, welches den Benutzernamen enthält. (Beispiel: uid oder sAMAccountName)');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateData'] = array('Daten aus dem LDAP automatisch aktualisieren', 'Diese Checkbox aktivieren, falls die LDAP-Daten jede Minute per Contao Cron aktualisiert werden sollen.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['updateFilter'] = array('Updatefilter', 'Hier kann gefiltert werden welche Mitglieder aus dem LDAP regelmäßig importiert und aktualisiert werden sollen. (Beispiel: (&(objectClass=user)) )');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['groupFilterCheck'] = array('Filter für Gruppen', 'Diese Checkbox aktivieren, falls die Gruppenzugehörigkeiten nicht über das Attribut memberOf ermittelt werden können. Stattdessen werden hierüber die Gruppen des genannten Filter durchsucht.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['groupFilter'] = array('Gruppenfilter', 'Hier kann gefiltert werden, bei welchen Einträgen es sich um Gruppen handelt. (Beispiel: (&(objectClass=groupOfNames)) )');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['linkWithUserMail'] = array('Migriere bestehenden Nutzer', 'Falls bereits ein Nutzer mit der selben Mailadresse besteht, wird dieser mit dem LDAP Nutzer zusammengeführt. Ein Login ist danach nur noch über den LDAP Nutzer möglich, der Nutzername wird angepasst.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['twoDirectionalSync'] = array('Daten im LDAP aktualisieren', 'Diese Checkbox aktivieren, falls Mitgliederänderungen aus dem FrontEnd an das LDAP übertragen werden sollen.');
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['c4gLdapRegistration'] = ['Mitglied im LDAP verknüpfen', 'Diese Checkbox aktivieren, falls die Mitglieder im LDAP angelegt/angepasst werden sollen.'];
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['c4gLdapRegistrationOu'] = ['OU der Mitglieder', 'Hier den DN-String der OU angeben, an der die Mitglieder gespeichert werden sollen.'];
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['registration'] = 'Registrierung';

    /** Options */
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['plain'] = 'Ohne Verschlüsselung';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['ssl'] = 'SSL';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['tls'] = 'TLS';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['windows_ad'] = 'Windows AD';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['openldap'] = 'OpenLDAP';

$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['bindError'] = 'Es konnte keine Verbindung mit dem LDAP Server aufgebaut werden.';
$GLOBALS['TL_LANG']['tl_c4g_ldap_settings']['infotext'] =
    'Die Gruppen werden bei jedem speichern dieser Einstellungen aktualisiert. Die Zuordnung der Gruppen zu den Usern erfolgt bei jedem Login. Falls der Filter geändert wird, werden dann nicht mehr vorhandene Gruppen automatisch gelöscht.';
