<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Controller\ProposalsController;
use Supplies\Model\Proposals;

class ProposalsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ProposalsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Proposals::class)
        );
    }
}