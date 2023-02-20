<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\GroupsController;
use Admin\Model\Groups;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Mcas;
use Admin\Model\McasGroups;
use Admin\Model\GroupsPositionsUsers;
use Admin\Model\McasUsers;
use Admin\Model\McasUsersDeny;
use Settings\Model\Companies;

class GroupsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new GroupsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Groups::class),
            $container->get(Mcas::class),
            $container->get(McasGroups::class),
            $container->get(GroupsPositionsUsers::class),
            $container->get(McasUsers::class),
            $container->get(Companies::class),
            $container->get(McasUsersDeny::class)
        );
    }
}