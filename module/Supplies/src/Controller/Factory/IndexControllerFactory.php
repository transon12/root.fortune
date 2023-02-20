<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Controller\IndexController;
use Supplies\Model\Supplies;
use Settings\Model\Companies;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Supplies::class),
            $container->get(Companies::class)
        );
    }
}