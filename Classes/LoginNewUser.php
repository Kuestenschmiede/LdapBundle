<?php

namespace con4gis\AuthBundle\Classes;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\MemberModel;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Contao\UserModel;

class LoginNewUser implements ServiceAnnotationInterface
{
    /**
     * @Hook("importUser")
     */
    public function importUserBeforeAuthenticate(string $username, string $password, string $table): bool
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*_-';
        $password = hash('sha384', substr(str_shuffle($chars), 0, 18));

//        if (TL_MODE == 'FE') {
//            $table = "tl_member";
//        }

        if ('tl_user' === $table) {
            // Import user from an LDAP server
            if (UserModel::findByUsername($username)) {
                return true;
            }
            $user = new UserModel();
        } elseif ('tl_member' === $table) {
            // Import user from an LDAP server
            if (MemberModel::findByUsername($username)) {
                return true;
            }
            $user = new MemberModel();
            $user->login = '1';
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
