<?php

namespace Shopping\ShellCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Workflow\Definition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    protected $name = 'shell_command';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->name);

        $rootNode
            ->children()
                ->arrayNode('commands')
                    ->useAttributeAsKey('key')
                    ->canBeUnset()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('output')
                                ->children()
                                    ->scalarNode('type')->end()
                                    ->scalarNode('path')->end()
                                ->end()
                            ->end()
                            ->arrayNode('args')
                                ->prototype('variable')->end()
                            ->end()
                            ->arrayNode('options')
                                ->prototype('variable')->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('pipes')
                    ->useAttributeAsKey('key')
                    ->canBeUnset()
                    ->prototype('variable')->end()
                        ->defaultValue([])
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
