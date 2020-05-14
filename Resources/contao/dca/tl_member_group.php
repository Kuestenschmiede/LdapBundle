<?php


use Contao\Backend;
use Contao\Image;
use Contao\Message;
use Contao\StringUtil;

/**
 * Edit On Load
 */


/**
 * Edit Operations
 */
$GLOBALS['TL_DCA']['tl_member_group']['list']['operations']['delete'] = array
(
    'href'                => 'act=delete',
    'icon'                => 'delete.svg',
    'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
    'button_callback'     => array('tl_member_group_con4gis', 'deleteUser')
);

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_member_group']['fields']['con4gisAuthMemberGroup'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

class tl_member_group_con4gis extends Contao\Backend
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
    public function deleteUser($row, $href, $label, $title, $icon, $attributes)
    {
        if ($row['con4gisAuthMemberGroup'] == "1") {
            return "";
        } else {
            return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . Contao\StringUtil::specialchars($title) . '"' . $attributes . '>' . Contao\Image::getHtml($icon, $label) . '</a> ';
        }
    }
}