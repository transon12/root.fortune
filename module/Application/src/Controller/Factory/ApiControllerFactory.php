<?php

namespace Application\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Controller\ApiController;
use Codes\Model\Codes;
use Settings\Model\Settings;
use Storehouses\Model\Products;
use Settings\Model\Messages;
use Settings\Model\Phones;
use Codes\Model\Blocks;
use Promotions\Model\ListPromotions;
use Promotions\Model\Promotions;
use Settings\Model\LogSms;
use Settings\Model\Companies;
use Storehouses\Model\Agents;
use Settings\Model\Logs;
use Settings\Model\StatisticChecks;
use Statistics\Model\TableStatistics;

class ApiControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ApiController(
            $container->get(Codes::class),
            $container->get(Settings::class),
            $container->get(Products::class),
            $container->get(Messages::class),
            $container->get(Phones::class),
            $container->get(Blocks::class),
            $container->get(Promotions::class),
            $container->get(LogSms::class),
            $container->get(Companies::class),
            $container->get(Agents::class),
            $container->get(Logs::class),
            $container->get(StatisticChecks::class),
            $container->get(ListPromotions::class),
            $container->get(TableStatistics::class)
        );
    }
}