<?php

namespace DataApi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\NewsController;
use Admin\Model\PxtAuthentication;
use DataApi\Model\News;

class NewsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new NewsController(
            $container->get(PxtAuthentication::class),
            $container->get(News::class)
        );
    }
}