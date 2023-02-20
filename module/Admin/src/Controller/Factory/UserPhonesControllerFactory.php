<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Users;
use Admin\Controller\UserPhonesController;
use Admin\Model\UserPhones;

class UserPhonesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new UserPhonesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(UserPhones::class),
            $container->get(Users::class)
        );
    }
}