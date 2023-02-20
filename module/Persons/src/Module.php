<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Persons;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    public function getServiceConfig(){
        return [
            'factories' => [
                Model\Profiles::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Profiles($adapter);
                },
                Model\Recruitments::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Recruitments($adapter);
                },
                Model\Trainings::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Trainings($adapter);
                },
                Model\RewardDisciplines::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\RewardDisciplines($adapter);
                },
                Model\LeaveLists::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\LeaveLists($adapter);
                },
                Model\LeaveRequests::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\LeaveRequests($adapter);
                },
                Model\EvaluationCriterias::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\EvaluationCriterias($adapter);
                },
                Model\UserKpis::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\UserKpis($adapter);
                },
                Model\Notifications::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Notifications($adapter);
                },
                Model\TimeKeeping::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\TimeKeeping($adapter);
                },
                Model\Evaluations::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\Evaluations($adapter);
                },
                Model\ManageEvaluations::class => function($container) {
                    $adapter = $container->get('connect_db');
                    return new Model\ManageEvaluations($adapter);
                },
            ],
        ];
    }
}
