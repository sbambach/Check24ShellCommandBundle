<?php

namespace Check24\ShellCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class Configuration implements ConfigurationInterface
{
    protected $name = 'check24_shell_command';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        if (method_exists(TreeBuilder::class, 'root')) {
            $treeBuilder = new TreeBuilder();
            $rootNode = $treeBuilder->root($this->name);
        } else {
            $treeBuilder = new TreeBuilder($this->name);
            $rootNode = $treeBuilder->getRootNode();
        }

        $rootNode
            ->children()
                ->arrayNode('commands')
                    ->useAttributeAsKey('key')
                    ->canBeUnset()
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->arrayNode('output')
                                ->prototype('variable')->end()
                            ->end()
                            ->arrayNode('expectedExitCodes')
                                ->prototype('integer')->end()
                                ->defaultValue([0])
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
