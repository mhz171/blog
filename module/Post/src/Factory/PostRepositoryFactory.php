<?php

namespace Post\Factory;

use Doctrine\ORM\EntityManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class PostRepositoryFactory implements FactoryInterface
{
    public function __invoke($container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        return new PostRepository($entityManager);
    }
}