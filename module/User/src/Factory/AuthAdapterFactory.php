<?php
// module/User/src/Factory/AuthAdapterFactory.php


namespace User\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use User\Service\AuthAdapter;
use Doctrine\ORM\EntityManager;

class AuthAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        return new AuthAdapter($entityManager);
    }
}
