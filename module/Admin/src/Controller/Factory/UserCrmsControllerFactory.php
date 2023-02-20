<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\UserCrmsController;
use Admin\Model\Users;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\UserCrms;

class UserCrmsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new UserCrmsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(UserCrms::class),
            $container->get(Users::class)
        );
    }
}