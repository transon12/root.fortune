<?php

namespace Promotions\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Promotions\Controller\OrderController;
use Admin\Model\PxtAuthentication;
use Admin\Model\UserCrms;
use Admin\Model\Users;
use Storehouses\Model\Products;
use Promotions\Model\Promotions;
use Promotions\Model\PromotionsProducts;
use Settings\Model\Settings;
use Promotions\Model\ListPromotions;
use Promotions\Model\WinnerPromotions;
use Promotions\Model\ListDials;
use Promotions\Model\DialsPromotions;
use Promotions\Model\LogWinners;
use Promotions\Model\WinnerDials;
use Settings\Model\Companies;

class OrderControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new OrderController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Products::class),
            $container->get(Promotions::class),
            $container->get(PromotionsProducts::class),
            $container->get(ListPromotions::class),
            $container->get(WinnerPromotions::class),
            $container->get(ListDials::class),
            $container->get(DialsPromotions::class),
            $container->get(WinnerDials::class),
            $container->get(Companies::class),
            $container->get(Users::class),
            $container->get(UserCrms::class),
            $container->get(LogWinners::class)
        );
    }
}