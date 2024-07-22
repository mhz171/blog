<?php

namespace Post\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Post\Model\PostTable;


class PostTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $tableGateway = $container->get(\Post\Model\PostTableGateway::class);
        return new PostTable($tableGateway);
    }
}
