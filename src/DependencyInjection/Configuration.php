<?php

namespace Moukail\AppleWalletPassBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('moukail_apple_wallet_pass');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->scalarNode('pass_repository')
            ->isRequired()
            ->info('A class that implements PassRepositoryInterface - usually your PassRepository.')
            ->end()
            ->scalarNode('pass_service')
            ->isRequired()
            ->info('A class that implements PassServiceInterface - usually your PassService.')
            ->end()
            //->scalarNode('email_base_url')
            //->isRequired()
            //->info('Base email url')
            //->end()
        ;

        return $treeBuilder;
    }
}
