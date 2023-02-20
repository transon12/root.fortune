<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Settings\Model\Settings;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Storehouses\Model\Storehouses;
use Storehouses\Controller\IndexController;
use Settings\Model\Companies;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Storehouses::class),
            $container->get(Countries::class),
            $container->get(Cities::class),
            $container->get(Districts::class),
            $container->get(Wards::class),
            $container->get(Companies::class),
            $container->get(Users::class)
        );
    }
}