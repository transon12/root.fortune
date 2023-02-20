<?php

namespace Companies\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Controller\OrdersController;
use Companies\Model\Addresses;
use Companies\Model\MissionDetails;
use Companies\Model\Missions;
use Companies\Model\OrderDetails;
use Companies\Model\Orders;
use Companies\Model\Surrogates;
use Interop\Container\ContainerInterface;
use Settings\Model\Companies;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrdersControllerFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new OrdersController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Orders::class),
            $container->get(Companies::class),
            $container->get(Addresses::class),
            $container->get(Surrogates::class),
            $container->get(MissionDetails::class),
            $container->get(OrderDetails::class),
            $container->get(Users::class),
            $container->get(Missions::class),
            $container->get(Technologies::class)
        );
    }
}