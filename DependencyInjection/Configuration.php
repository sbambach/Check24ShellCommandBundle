<?php

namespace Shopping\ShellCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    protected $name = 'PipeBuilder';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);

        $rootNode
            ->children()
                ->arrayNode('Commands')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('args')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('url')->end()
                            ->arrayNode('options')
                                ->prototype('array')
                                    ->children()

            ->end()
        ;

        return $treeBuilder;
    }
}
