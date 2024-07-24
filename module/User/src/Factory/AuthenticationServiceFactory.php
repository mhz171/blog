<?php
// module/User/src/Factory/AuthenticationServiceFactory.php

namespace User\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Storage\Session as SessionStorage;
use Laminas\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as AuthAdapter;

class AuthenticationServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $dbAdapter = $container->get('Laminas\Db\Adapter\Adapter');
        $authAdapter = new AuthAdapter($dbAdapter, 'users', 'username', 'password');

        return new AuthenticationService(new SessionStorage('user_session'), $authAdapter);
    }
}
