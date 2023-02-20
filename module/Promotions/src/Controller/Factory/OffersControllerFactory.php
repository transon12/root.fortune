<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Promotions\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Settings\Model\Settings;
use Promotions\Controller\OffersController;
use Promotions\Model\Offers;
use Storehouses\Model\Products;

class OffersControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new OffersController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Offers::class),
            $container->get(Products::class)
        );
    }
}