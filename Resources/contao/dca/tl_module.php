<?php
/**
 * This file is part of con4gis,
 * the gis-kit for Contao CMS.
 *
 * @package   	con4gis
 * @version        6
 * @author  	    con4gis contributors (see "authors.txt")
 * @license 	    LGPL-3.0-or-later
 * @copyright 	Küstenschmiede GmbH Software & Design
 * @link              https://www.con4gis.org
 *
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField(['c4gLdapRegistration'], 'config_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('registration', 'tl_module');

$GLOBALS['TL_DCA']['tl_module']['fields']['c4gLdapRegistration'] = [
    'exclude'                 => true,
    'filter'                  => true,
    'inputType'               => 'checkbox',
    'sql'                     => "char(1) NOT NULL default ''"
];