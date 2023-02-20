<?php
namespace Promotions\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Promotions\Model\Dials;
use Promotions\Model\Prizes;
use Promotions\Controller\PrizesController;
use Promotions\Model\WinnerDials;

class PrizesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new PrizesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Dials::class),
            $container->get(Prizes::class),
            $container->get(WinnerDials::class)
        );
    }
}