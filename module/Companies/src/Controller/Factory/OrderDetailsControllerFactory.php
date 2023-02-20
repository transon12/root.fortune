<?php

namespace Companies\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Companies\Controller\OrderDetailsController;
use Companies\Model\OrderDetails;
use Companies\Model\Orders;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Settings\Model\Technologies;
use Zend\ServiceManager\Factory\FactoryInterface;

class OrderDetailsControllerFactory implements FactoryInterface{

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        return new OrderDetailsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Orders::class),
            $container->get(OrderDetails::class),
            $container->get(Technologies::class),
            $container->get(Users::class)
        );
    }
}