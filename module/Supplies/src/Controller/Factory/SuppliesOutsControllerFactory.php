<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Model\ProposalDetails;
use Supplies\Model\Supplies;
use Supplies\Controller\SuppliesOutsController;
use Storehouses\Model\Storehouses;
use Supplies\Model\SuppliesIns;
use Supplies\Model\SuppliesOuts;

class SuppliesOutsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new SuppliesOutsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(SuppliesOuts::class),
            $container->get(ProposalDetails::class),
            $container->get(Supplies::class),
            $container->get(Storehouses::class),
            $container->get(SuppliesIns::class)
        );
    }
}