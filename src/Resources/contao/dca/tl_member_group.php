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
$GLOBALS['TL_DCA']['tl_member_group']['config']['onload_callback'][] = [
    LdapCallback::class, 'onLoadMemberGroup'
];

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_member_group']['fields']['con4gisLdapMemberGroup'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);