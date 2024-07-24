<?php
// module/User/src/Factory/DoctrineAdapterFactory.php

namespace User\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use User\Service\DoctrineAdapter;
use Doctrine\ORM\EntityManager;

class DoctrineAdapterFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        return new DoctrineAdapter($entityManager);
    }
}
