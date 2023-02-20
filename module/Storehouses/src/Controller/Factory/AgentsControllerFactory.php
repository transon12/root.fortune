<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Storehouses\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Agents;
use Settings\Model\Settings;
use Settings\Model\Phones;
use Codes\Model\Codes;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;
use Admin\Model\Users;
use Settings\Model\Countries;
use Settings\Model\Cities;
use Settings\Model\Districts;
use Settings\Model\Wards;
use Storehouses\Controller\AgentsController;
use Settings\Model\Companies;

class AgentsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new AgentsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Agents::class),
            $container->get(Phones::class),
            $container->get(Codes::class),
            $container->get(Products::class),
            $container->get(Storehouses::class),
            $container->get(Users::class),
            $container->get(Countries::class),
            $container->get(Cities::class),
            $container->get(Districts::class),
            $container->get(Wards::class),
            $container->get(Companies::class)
        );
    }
}