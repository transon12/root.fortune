<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Products;
use Settings\Model\Settings;
use Codes\Model\Blocks;
use Settings\Model\Messages;
use Codes\Model\Codes;
use Promotions\Model\PromotionsProducts;
use Settings\Model\Companies;
use Storehouses\Controller\ProductsController;

class ProductsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ProductsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Products::class),
            $container->get(Blocks::class),
            $container->get(Messages::class),
            $container->get(Codes::class),
            $container->get(PromotionsProducts::class),
            $container->get(Companies::class)
        );
    }
}