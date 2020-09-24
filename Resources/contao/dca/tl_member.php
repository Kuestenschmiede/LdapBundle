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
    ->addField(['c4g_internalUserId'], 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member');

Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('c4g_company_data', 'personal_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER, true)
    ->addField(['c4g_department', 'c4g_room'], 'c4g_company_data', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
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

$GLOBALS['TL_DCA']['tl_member']['fields']['c4g_internalUserId'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['c4g_internalUserId'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['c4g_department'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['c4g_department'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_member']['fields']['c4g_room'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_member']['c4g_room'],
    'sorting'                 => true,
    'search'                  => true,
    'inputType'               => 'text',
    'default'                 => '',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(100) NOT NULL default ''"
);