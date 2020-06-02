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
$GLOBALS['BE_MOD']['con4gis_ldap'] =  array(
    'be_groups' => array
    (
        'tables'    => array('tl_c4g_ldap_be_groups'),
    ),
    'fe_groups' => array
    (
        'tables'    => array('tl_c4g_ldap_fe_groups'),
    ),
    'c4g_ldap_settings' => array
    (
        'tables'    => array('tl_c4g_ldap_settings'),
    )
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['importUser'][] = array('con4gis\LdapBundle\Classes\LoginNewUser', 'importUserBeforeAuthenticate');