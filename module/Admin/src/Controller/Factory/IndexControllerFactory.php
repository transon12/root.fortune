<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Admin\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Controller\IndexController;
use Admin\Model\PxtAuthentication;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class)
        );
    }
}