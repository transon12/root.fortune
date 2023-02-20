<?php
namespace Promotions\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Promotions\Model\Promotions;
use Promotions\Controller\ListDialsController;
use Settings\Model\Settings;
use Promotions\Model\ListDials;
use Settings\Model\Phones;
use Promotions\Model\WinnerDials;

class ListDialsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ListDialsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Promotions::class),
            $container->get(ListDials::class),
            $container->get(Phones::class),
            $container->get(WinnerDials::class)
        );
    }
}