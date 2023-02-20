<?php

namespace Companies\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Controller\MissionDetailsController;
use Companies\Model\MissionDetails;
use Companies\Model\Missions;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class MissionDetailsControllerFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new MissionDetailsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Missions::class),
            $container->get(MissionDetails::class),
            $container->get(Users::class)
        );
    }
}