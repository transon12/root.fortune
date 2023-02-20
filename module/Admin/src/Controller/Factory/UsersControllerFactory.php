<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\UsersController;
use Admin\Model\Users;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Groups;
use Admin\Model\Positions;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\Mcas;
use Settings\Model\Companies;
use Admin\Model\McasUsersDeny;
use Admin\Model\McasUsersAllow;
use Persons\Model\LeaveLists;
use Persons\Model\Profiles;

class UsersControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new UsersController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Users::class),
            $container->get(Groups::class),
            $container->get(Positions::class),
            $container->get(GroupsPositionsUsers::class),
            $container->get(Mcas::class),
            $container->get(McasUsersDeny::class),
            $container->get(McasUsersAllow::class),
            $container->get(Companies::class),
            $container->get(Profiles::class),
            $container->get(LeaveLists::class)
        );
    }
}