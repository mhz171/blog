<?php

namespace Post\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Post\Repository\PostRepository;
use Post\Service\PostService;


class PostServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get(EntityManager::class);
        $postRepository = $container->get(PostRepository::class);
        return new PostService($entityManager, $postRepository);
    }
}