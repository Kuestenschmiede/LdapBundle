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
 */
namespace con4gis\LdapBundle\Classes;

use con4gis\LdapBundle\Resources\contao\models\LdapMemberModel;
use con4gis\LdapBundle\Resources\contao\models\LdapUserModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Message;

class LdapCallback {
    /**
     * Remove password field, make username and mail read-only if LDAP User/Member
     *
     * @Callback(table="tl_user", target="config.onload")
     * @Callback(table="tl_member", target="config.onload")
     * @param DataContainer $dc
     */
    public function onLoadCallback(DataContainer $dc) : void {
        if($dc->id == null)
            return;


        $currentRecord = null;

        if ($dc->table == 'tl_user') {
            $currentRecord = LdapUserModel::findById($dc->id);
            if($currentRecord == null || $currentRecord->con4gisLdapUser == 0)
                return;

        } elseif ($dc->table == 'tl_member') {
            $currentRecord = LdapMemberModel::findById($dc->id);
            if($currentRecord == null || $currentRecord->con4gisLdapMember == 0)
                return;

        }

            foreach (['username', 'email'] as $field) {
                if(!array_key_exists('eval', $GLOBALS['TL_DCA']['tl_user']['fields'][$field]))
                    $GLOBALS['TL_DCA']['tl_user']['fields'][$field]['eval'] = [];

                $GLOBALS['TL_DCA']['tl_user']['fields'][$field]['eval']['readonly'] = true;
            }

            if($dc->table == 'tl_user') {
                foreach (['login', 'admin', 'default', 'group', 'extend', 'custom'] as $palette) {
                    PaletteManipulator::create()
                        ->removeField('password', 'password_legend')
                        ->removeField('pwChange')
                        ->applyToPalette($palette, 'tl_user');
                }
            } elseif ($dc->table == 'tl_member') {
                PaletteManipulator::create()
                    ->removeField('password')
                    ->applyToSubpalette('login', 'tl_member');
            }

            Message::addInfo($GLOBALS['TL_LANG'][$dc->table]['ldap_readonly_info']);
        }
}