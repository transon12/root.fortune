<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Controller\UserAddressesController;
use Admin\Model\UserAddresses;
use Admin\Model\Users;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;

class UserAddressesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new UserAddressesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(UserAddresses::class),
            $container->get(Users::class),
            $container->get(Countries::class),
            $container->get(Cities::class),
            $container->get(Districts::class),
            $container->get(Wards::class)
        );
    }
}