<?php
namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\PositionsController;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Admin\Model\Positions;
use Admin\Model\GroupsPositionsUsers;
use Settings\Model\Companies;

class PositionsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new PositionsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Positions::class),
            $container->get(GroupsPositionsUsers::class),
            $container->get(Companies::class)
        );
    }
}