<?php

namespace Karhal\Web3ConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('web3_connect');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('user_class')->defaultValue('App\\Entity\\User')->end()
            ->scalarNode('jwt_secret')->defaultValue('ThisIsNotASecret')->end()
            ->scalarNode('jwt_algo')->defaultValue('HS256')->end()
            ->scalarNode('http_header')->defaultValue('X-AUTH-WEB3TOKEN')->end()
            ->scalarNode('ttl')->defaultValue(86400)->end()
            ->scalarNode('sign_message')->defaultValue('Sign this message to prove you have access to this wallet.')->end()
            ->end();

        return $treeBuilder;
    }
}
