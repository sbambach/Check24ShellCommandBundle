<?php

namespace Shopping\ShellCommandBundle\DependencyInjection;

use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;
use Shopping\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponentBuilder;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentBuilder;
use Shopping\ShellCommandBundle\Utils\Pipe\Pipe;
use Shopping\ShellCommandBundle\Utils\Pipe\PipeFactory;
use Shopping\ShellCommandBundle\Utils\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class ShellCommandExtension extends Extension implements PrependExtensionInterface
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
//        $loader->load('config.yml');
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

        $commandDefinitions = $this->createCommands($container, $config, $commandDefinitions);

        $processManagerDefinition = new Definition(ProcessManager::class);
        $processManagerDefinition->addArgument(new Reference('logger'));

        $this->createPipes($container, $config, $commandDefinitions, $processManagerDefinition);
    }

    public function prepend(ContainerBuilder $container)
    {
        $config =
            [
                'commands' =>
                    [
                        'tee' =>
                            [
                                'name' => 'tee',
                                'args' =>
                                    [
                                        '${filePath}',
                                    ],
                            ],
                    ],
            ];

        $container->prependExtensionConfig('shell_command', $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param                  $commandDefinitions
     *
     * @return array
     */
    protected function createCommands(ContainerBuilder $container, array $config, $commandDefinitions): array
    {
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

        return $commandDefinitions;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param                  $commandDefinitions
     * @param                  $processManagerDefinition
     */
    protected function createPipes(
        ContainerBuilder $container,
        array $config,
        $commandDefinitions,
        $processManagerDefinition
    ): void {
        foreach ($config['pipes'] as $pipeName => $commands) {
            $pipeParts = [];
            foreach ($commands as $id => $commandNames) {
                foreach ($commandNames as $commandName) {
                    $commandName = str_replace('-', '_', $commandName);
                    $pipeParts[$id][] = $commandDefinitions[$commandName];
                }
            }
            unset($commands, $id);

            $loggerReference = new Reference('logger');

            $pipeComponents = [];

            foreach ($pipeParts as $id => $commands) {
                $linearPipeComponent = $teePipeComponent = null;
                foreach ($commands as $index => $command) {
                    $processDefinition = new Definition(Process::class, [$command]);

                    if ($index === 0) {
                        $linearPipeComponent = new Definition(
                            LinearPipeComponent::class,
                            [$loggerReference, $processDefinition]
                        );

                        $linearPipeComponent->setFactory([LinearPipeComponentBuilder::class, 'build']);

                        $pipeComponents[$id][] = $linearPipeComponent;
                    } elseif ($index === 1) {
                        $teeCommandDefinition = $container->getDefinition('shell_command.commands.tee');
                        $teeProcessDefinition = new Definition(Process::class, [$teeCommandDefinition]);

                        $teePipeComponent = new Definition(
                            TeePipeComponent::class,
                            [$linearPipeComponent, $loggerReference, $teeProcessDefinition]
                        );

                        $teePipeComponent->setFactory([TeePipeComponentBuilder::class, 'build']);

                        $pipeComponents[$id][] = $teePipeComponent;
                    }

                    if ($index >= 1) {
                        $teePipeComponent->addMethodCall('addFileProcess', [$processDefinition]);
                    }
                }
            }

            $pipeDefinition = new Definition(
                Pipe::class,
                [
                    $pipeName,
                    $pipeComponents,
                    $processManagerDefinition,
                    $loggerReference,
                ]
            );

            $pipeDefinition->setFactory([PipeFactory::class, 'createPipe']);
            $container->setDefinition(sprintf('shell_command.pipes.%s', $pipeName), $pipeDefinition);
        }
    }
}
