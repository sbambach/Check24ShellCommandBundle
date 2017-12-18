<?php

namespace Shopping\ShellCommandBundle\DependencyInjection;

use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;
use Shopping\ShellCommandBundle\Utils\Pipe\Pipe;
use Shopping\ShellCommandBundle\Utils\Pipe\PipeFactory;
use Shopping\ShellCommandBundle\Utils\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ShellCommandExtension extends Extension
{
//    public function prepend(ContainerBuilder $container)
//    {
//        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
//        $loader->load('config.yml');
//
//        $container->prependExtensionConfig($this->getAlias(), $config);
//    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->updateContainerParameters($container, $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');


    }
    /**
     * Update parameters using configuratoin values.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function updateContainerParameters(ContainerBuilder $container, array $config)
    {
        $commandDefinitions = [];

        foreach ($config['commands'] as $commandName => $command) {
            $options = [];
            foreach ($command['options'] as $option) {
                if (is_scalar($option)) {
                    $options[] = $option;
                } else if (is_array($option)) {
                    $options[key($option)] = current($option);
                }
            }

            $commandDefinition = new Definition(
                ParameterCommand::class,
                [$command['name'], $command['args'] ?? [], $options ?? []]
            );

            $container->setDefinition(sprintf('shell_command.commands.%s', $commandName), $commandDefinition);
            $commandDefinitions[$commandName] = $commandDefinition;
        }

        $processManagerDefinition = new Definition(ProcessManager::class);
        $processManagerDefinition->addArgument(new Reference('logger'));

        foreach ($config['pipes'] as $pipeName => $commands) {
            $neededCommands = [];
            foreach ($commands as $id => $commandNames) {
                foreach ($commandNames as $commandName) {
                    $commandName = str_replace('-', '_', $commandName);
                    $neededCommands[$id][] = $commandDefinitions[$commandName];
                }
            }

            $pipeDefinition = new Definition(
                Pipe::class,
                [$pipeName, $neededCommands, $processManagerDefinition, new Reference('logger')]
            );

            $pipeDefinition->setFactory([PipeFactory::class, 'createPipe']);
            $container->setDefinition(sprintf('shell_command.pipes.%s', $pipeName), $pipeDefinition);
        }
    }
}
