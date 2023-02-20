<?php

namespace Statistics\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Statistics\Controller\DialsController;
use Promotions\Model\ListDials;
use Promotions\Model\Promotions;
use Promotions\Model\Dials;
use Promotions\Model\WinnerDials;
use Promotions\Model\Prizes;

class DialsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new DialsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(ListDials::class),
            $container->get(Promotions::class),
            $container->get(Dials::class),
            $container->get(WinnerDials::class),
            $container->get(Prizes::class)
        );
    }
}