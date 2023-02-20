<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Controller\IndexController;
use Settings\Model\Settings;
use Settings\Model\Companies;
use Settings\Model\CompanyConfigs;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Companies::class),
            $container->get(CompanyConfigs::class)
        );
    }
}