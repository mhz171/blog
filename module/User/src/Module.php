<?php

namespace User;

use Post\Factory\PostControllerFactory;
use Post\Factory\PostTableFactory;
use Post\Factory\PostTableGatewayFactory;
use Post\Model;
use Post\Model\PostTable;
use Post\Model\Post;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Post\Controller\PostController;


class Module implements ConfigProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }


}
