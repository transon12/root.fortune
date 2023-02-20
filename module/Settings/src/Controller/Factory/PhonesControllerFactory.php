<?php
namespace Settings\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Settings\Controller\PhonesController;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Settings\Model\Phones;
use Storehouses\Model\Agents;

class PhonesControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new PhonesController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Phones::class),
            $container->get(Agents::class)
        );
    }
}