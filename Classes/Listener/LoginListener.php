<?php


namespace con4gis\AuthBundle\Classes\Listener;


use Contao\Backend;
use Contao\Controller;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Contao\UserModel;
use Contao\Model;

class LoginListener
{
    public function onInteractiveLogin (InteractiveLoginEvent $event) {

        if (TL_MODE == 'BE') {

            $loginUsername = $event->getAuthenticationToken()->getUsername();
            $beUser = UserModel::findByUsername($loginUsername);

            if ($beUser) {
                return "";
            } else {

//                $user = new UserModel();
//                $user->username = $loginUsername;
//                $user->save();

                Controller::redirect("/contao/logout");

            }

            echo "sagfhdlsjadfk";

        } elseif (TL_MODE == 'FE') {
            return "";
        }

    }

    public function onLoginFailure (AuthenticationFailureEvent $event) {
        $test = "testdate";
        echo "sagfhdlsjadfk";
    }

    public function onLogout (DeauthenticatedEvent $event) {
        $test = "testdate";
        echo "sagfhdlsjadfk";
    }
}