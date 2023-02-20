<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Promotions\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Promotions\Controller\DialsController;
use Admin\Model\PxtAuthentication;
use Promotions\Model\Dials;
use Settings\Model\Settings;
use Promotions\Model\Promotions;
use Promotions\Model\DialsPromotions;
use Promotions\Model\Prizes;
use Promotions\Model\ListDials;
use Promotions\Model\WinnerDials;
use Settings\Model\LogSms;
use Settings\Model\Companies;

class DialsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new DialsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Dials::class),
            $container->get(Promotions::class),
            $container->get(DialsPromotions::class),
            $container->get(Prizes::class),
            $container->get(ListDials::class),
            $container->get(WinnerDials::class),
            $container->get(LogSms::class),
            $container->get(Companies::class)
        );
    }
}