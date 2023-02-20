<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Supplies\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Admin\Model\PxtAuthentication;
use Settings\Model\Settings;
use Supplies\Controller\ProposalDetailsController;
use Supplies\Model\ProposalDetails;
use Supplies\Model\Proposals;
use Supplies\Model\Supplies;
use Storehouses\Model\Storehouses;

class ProposalDetailsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new ProposalDetailsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(ProposalDetails::class),
            $container->get(Proposals::class),
            $container->get(Supplies::class),
            $container->get(Storehouses::class)
        );
    }
}