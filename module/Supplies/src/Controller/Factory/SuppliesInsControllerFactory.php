<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Controller\SuppliesInsController;
use Supplies\Model\SuppliesIns;
use Storehouses\Model\Storehouses;
use Supplies\Model\Supplies;
use Supplies\Model\Suppliers;

class SuppliesInsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new SuppliesInsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(SuppliesIns::class),
            $container->get(Storehouses::class),
            $container->get(Supplies::class),
            $container->get(Suppliers::class)
        );
    }
}