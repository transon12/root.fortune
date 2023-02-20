<?php
/**
 * @copyright xuanthieu.cse@gmail.com
 */

namespace Codes\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

use Codes\Controller\IndexController;
use Admin\Model\PxtAuthentication;
use Storehouses\Model\Products;
use Codes\Model\Blocks;
use Codes\Model\Codes;
use Codes\Model\CodeRoots;
use Codes\Model\CodeFiles;
use Settings\Model\Settings;
use Codes\Model\CodeRootsQrcode;
use Codes\Model\CodeFilesQrcode;
use Settings\Model\Companies;
use Settings\Model\CompanyConfigs;

class IndexControllerFactory implements FactoryInterface{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null){
        return new IndexController(
            $container->get(PxtAuthentication::class),
            $container->get(Settings::class),
            $container->get(Blocks::class),
            $container->get(Products::class),
            $container->get(Codes::class),
            $container->get(CodeRoots::class),
            $container->get(CodeFiles::class),
            $container->get(CodeRootsQrcode::class),
            $container->get(CodeFilesQrcode::class),
            $container->get(Companies::class),
            $container->get(CompanyConfigs::class)
        );
    }
}