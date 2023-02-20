<?php
namespace Storehouses\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Admin\Model\Users;
use Codes\Model\Codes;
use Interop\Container\ContainerInterface;
use Settings\Model\Settings;
use Storehouses\Controller\BillsController;
use Storehouses\Controller\DetailsController;
use Storehouses\Model\Agents;
use Storehouses\Model\BillDetails;
use Storehouses\Model\Bills;
use Storehouses\Model\Products;
use Storehouses\Model\Storehouses;
use Zend\ServiceManager\Factory\FactoryInterface;

class DetailsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DetailsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Storehouses::class),
            $container->get(Products::class),
            $container->get(Agents::class),
            $container->get(Bills::class),
            $container->get(Users::class),
            $container->get(BillDetails::class),
            $container->get(Codes::class)
        );
    }
}