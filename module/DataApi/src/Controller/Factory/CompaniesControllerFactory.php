<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use DataApi\Controller\CompaniesController;
use DataApi\Model\Companies;
use DataApi\Model\CompanyConfigs;

class CompaniesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new CompaniesController(
            $container->get(PxtAuthentication::class),
            $container->get(Companies::class),
            $container->get(CompanyConfigs::class)
        );
    }
}