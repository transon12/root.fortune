<?php

namespace DataApi\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\UserController;
use Admin\Model\PxtAuthentication;
use DataApi\Model\Users;
use Settings\Model\Companies;
use Settings\Model\CompanyConfigs;

class UserControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new UserController(
            $container->get(PxtAuthentication::class),
            $container->get(Companies::class),
            $container->get(CompanyConfigs::class),
            $container->get(Users::class)
        );
    }
}