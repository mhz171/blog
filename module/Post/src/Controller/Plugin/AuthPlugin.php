<?php

namespace Post\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\Authentication\AuthenticationService;
use Laminas\Session\Container;
use User\Entity\User;

class AuthPlugin extends AbstractPlugin
{
//    private $authService;
    private $user;

    public function __construct()
    {
        $session = new Container('user');
        $user = $session->user;
        $this->user = $user;
    }

    public function isLoggedIn(): bool
    {
        return (bool)$this->user;
    }

    public function getUser(): ?user
    {
        return $this->user;
    }
}
