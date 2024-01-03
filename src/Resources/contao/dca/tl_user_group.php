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
use Contao\Image;
use Contao\Message;
use Contao\StringUtil;

/**
 * Edit Operations
 */
$GLOBALS['TL_DCA']['tl_user_group']['list']['operations']['delete']['button_callback'] = [
    'tl_user_group_con4gis', 'deleteUser'
];

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user_group']['fields']['con4gisLdapUserGroup'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

class tl_user_group_con4gis extends Contao\Backend
{
    /**
     * Return the delete user button
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteUser($row, $href, $label, $title, $icon, $attributes): string
    {
        if ($row['con4gisLdapUserGroup'] == "1") {
            return "";
        } else {
            return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label) . '</a> ';
        }
    }
}