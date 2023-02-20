<?php

namespace DataApi\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Companies\Model\Addresses;
use Companies\Model\Surrogates;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use DataApi\Controller\OrdersController;
use DataApi\Model\OrderDetails;
use DataApi\Model\Orders;
use DataApi\Model\Technologies;

class OrdersControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new OrdersController(
            $container->get(PxtAuthentication::class),
            $container->get(Orders::class),
            $container->get(Addresses::class),
            $container->get(Surrogates::class),
            $container->get(OrderDetails::class),
            $container->get(Technologies::class)
        );
    }
}