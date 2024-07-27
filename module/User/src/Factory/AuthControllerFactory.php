<?php
// module/User/src/Factory/AuthControllerFactory.php

namespace User\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use User\Controller\AuthController;
use Laminas\Authentication\AuthenticationService;
use User\Service\DoctrineAdapter;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authAdapter = $container->get(DoctrineAdapter::class);
        return new AuthController($authAdapter);
    }
}

