<?php


namespace con4gis\AuthBundle\Classes\Listener;


use Contao\Backend;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Contao\UserModel;
use Contao\Model;

class LoginListener
{
    public function onInteractiveLogin (InteractiveLoginEvent $event) {

       $user = UserModel::findAll();

        echo "sagfhdlsjadfk";

    }

    public function onLoginFailure (AuthenticationFailureEvent $event) {
        $test = "testdate";
        echo "sagfhdlsjadfk";
    }
}