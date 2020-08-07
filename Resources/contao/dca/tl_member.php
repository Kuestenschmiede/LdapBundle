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
use Contao\Backend;

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addField(['internalUserId'], 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('company_data', 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['department', 'room'], 'company_data', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_member']['fields']['con4gisLdapMember'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['internalUserId'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['internalUserId'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) unsigned NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['department'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['department'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) unsigned NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['room'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['room'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) unsigned NOT NULL default ''"
);