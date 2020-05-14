<?php

/**
 * con4gis - the gis-kit
 *
 * @version   php 7
 * @package   con4gis
 * @author    con4gis contributors (see "authors.txt")
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Küstenschmiede GmbH Software & Design 2011 - 2018
 * @link      https://www.kuestenschmiede.de
 */

$GLOBALS['BE_MOD']['con4gis_auth'] =  array(
    'be_groups' => array
    (
        'tables'    => array('tl_c4g_auth_be_groups'),
    ),
    'fe_groups' => array
    (
        'tables'    => array('tl_c4g_auth_fe_groups'),
    ),
    'c4g_auth_settings' => array
    (
        'tables'    => array('tl_c4g_auth_settings'),
    )
);

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['importUser'][] = array('con4gis\AuthBundle\Classes\LoginNewUser', 'importUserBeforeAuthenticate');