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

use con4gis\LdapBundle\Classes\LdapCallback;

/**
 * Register callbacks
 */
$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = [
    LdapCallback::class, 'onLoadCallback'
];
/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['con4gisLdapUser'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);