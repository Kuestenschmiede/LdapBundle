<?php


namespace con4gis\AuthBundle\Classes\Listener;


use Contao\Controller;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class LoginListener
{
    public function onSuccessfullAuthentication (AuthenticationEvent $event) {
        Controller::redirect('/test');
        echo "sagfhdlsjadfk";
    }
}