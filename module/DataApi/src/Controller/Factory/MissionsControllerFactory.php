<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\MissionsController;
use DataApi\Model\MissionDetails;
use DataApi\Model\Missions;

class MissionsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new MissionsController(
            $container->get(PxtAuthentication::class),
            $container->get(Missions::class),
            $container->get(MissionDetails::class)
        );
    }
}