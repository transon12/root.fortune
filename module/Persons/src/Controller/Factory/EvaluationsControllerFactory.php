<?php

namespace Persons\Controller\Factory;

use Admin\Model\PxtAuthentication;
use Interop\Container\ContainerInterface;
use Persons\Controller\EvaluationsController;
use Persons\Model\EvaluationCriterias;
use Persons\Model\Evaluations;
use Persons\Model\ManageEvaluations;
use Persons\Model\Profiles;
use Settings\Model\Settings;
use Zend\ServiceManager\Factory\FactoryInterface;

class EvaluationsControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null){
        return new EvaluationsController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(EvaluationCriterias::class),
            $container->get(Evaluations::class),
            $container->get(Profiles::class),
            $container->get(ManageEvaluations::class)
        );
    }
}