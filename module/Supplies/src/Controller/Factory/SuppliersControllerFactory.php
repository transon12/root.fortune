<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Controller\SuppliersController;
use Supplies\Model\Suppliers;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Settings\Model\Companies;

class SuppliersControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new SuppliersController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Suppliers::class),
            $container->get(Countries::class),
            $container->get(Cities::class),
            $container->get(Districts::class),
            $container->get(Wards::class),
            $container->get(Companies::class)
        );
    }
}