<?php

namespace Karhal\Web3ConnectBundle\DependencyInjection;

use Karhal\Web3ConnectBundle\Controller\Web3ConnectController;
use Karhal\Web3ConnectBundle\EventListener\RequestListener;
use Karhal\Web3ConnectBundle\Handler\JWTHandler;
use Karhal\Web3ConnectBundle\Handler\Web3WalletHandler;
use Karhal\Web3ConnectBundle\Security\Web3Authenticator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class Web3ConnectExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->register(JWTHandler::class)
            ->addMethodCall('setConfiguration', [$config]);

        $container->register(Web3ConnectController::class)
            ->addTag('controller.service_arguments')
            ->setAutowired(true)
            ->addMethodCall('setConfiguration', [$config]);

        $container->register(Web3WalletHandler::class)
            ->setAutowired(true)
            ->addMethodCall('setConfiguration', [$config]);

        $container->register(Web3Authenticator::class)
            ->setAutowired(true)
            ->addMethodCall('setConfiguration', [$config]);
        ;
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }
}
