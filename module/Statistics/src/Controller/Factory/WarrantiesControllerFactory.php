<?php
namespace Statistics\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Statistics\Model\Warranties;
use Codes\Model\Codes;
use Statistics\Controller\WarrantiesController;
use Admin\Model\Users;

class WarrantiesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new WarrantiesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Warranties::class),
            $container->get(Codes::class),
            $container->get(Users::class)
        );
    }
}