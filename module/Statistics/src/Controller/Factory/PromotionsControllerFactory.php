<?php

namespace Statistics\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Products\Model\Products;
use Promotions\Model\ListPromotions;
use Statistics\Controller\PromotionsController;
use Promotions\Model\Promotions;
use Promotions\Model\WinnerPromotions;

class PromotionsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new PromotionsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(ListPromotions::class),
            $container->get(Products::class),
            $container->get(Promotions::class),
            $container->get(WinnerPromotions::class)
        );
    }
}