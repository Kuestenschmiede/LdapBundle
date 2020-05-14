<?php

use Contao\Backend;

/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_member']['fields']['con4gisAuthMember'] = array
(
        'sorting'                 => true,
        'search'                  => true,
        'inputType'               => 'text',
        'default'                 => 0,
        'sql'                     => "int(10) unsigned NOT NULL default '0'"
);