<?php

use Contao\Backend;

/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['con4gisAuthUser'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);