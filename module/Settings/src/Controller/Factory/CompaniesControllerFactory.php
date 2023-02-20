<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Settings\Model\Companies;
use Settings\Model\Settings;
use Settings\Controller\CompaniesController;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;

class CompaniesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new CompaniesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Companies::class),
            $container->get(Cities::class),
            $container->get(Districts::class),
            $container->get(Wards::class),
            $container->get(Users::class)
        );
    }
}