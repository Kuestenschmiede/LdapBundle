<?php

namespace con4gis\AuthBundle\Classes;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\MemberModel;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\UserModel;
use con4gis\AuthBundle\Entity\Con4gisAuthSettings;
use con4gis\AuthBundle\Entity\Con4gisAuthBackendGroups;
use Contao\System;

class LoginNewUser implements ServiceAnnotationInterface
{
    /**
     * @Hook("importUser")
     */
    public function importUserBeforeAuthenticate(string $username, string $password, string $table): bool {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-";
        $password = hash("sha384", substr(str_shuffle($chars), 0, 18));

        if ('tl_user' === $table) {
            // Import user from an LDAP server
            $user = new UserModel();
        } else if ('tl_member' === $table) {
            $user = new MemberModel();
        } else {
            return false;
        }

        $user->username = $username;
        $user->password = $password;
        $user->dateAdded = time();
        $user->save();
        return true;
    }
}