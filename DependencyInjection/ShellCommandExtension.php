<?php

namespace Shopping\ShellCommandBundle\DependencyInjection;

use Shell\Commands\CommandInterface;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentFactory;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentFactory;
use Shopping\ShellCommandBundle\Utils\Pipe\Pipe;
use Shopping\ShellCommandBundle\Utils\Pipe\PipeConnector;
use Shopping\ShellCommandBundle\Utils\Pipe\PipeFactory;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\File;
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
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 * @link      http://symfony.com/doc/current/cookbook/bundles/extension.html
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
        $this->createPipes($container, $config, $commandDefinitions);
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
            $commandDefinition->addMethodCall('setName', [$commandName]);

            $container->setDefinition(sprintf('shell_command.commands.%s', $commandName), $commandDefinition);

            $commandDefinitions[$commandName] = [
                'definition' => $commandDefinition,
                'output' => $command['output'] ?? [],
            ];
        }

        return $commandDefinitions;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param array            $commandDefinitions
     */
    protected function createPipes(ContainerBuilder $container, array $config, $commandDefinitions): void
    {
        $loggerReference = new Reference('logger');

        foreach ($config['pipes'] as $pipeName => $commands) {
            $pipeParts = [];
            foreach ($commands as $id => $commandNames) {
                foreach ($commandNames as $commandName) {
                    $commandName = str_replace('-', '_', $commandName);
                    $pipeParts[$id][] = $commandDefinitions[$commandName];
                }
            }
            unset($commands, $id);

            $pipeComponents = [];

            $processManagerDefinition = new Definition(ProcessManager::class);
            $processManagerDefinition->addMethodCall('setLogger', [$loggerReference]);
            foreach ($pipeParts as $id => $commands) {
                $linearPipeComponent = $teePipeComponent = null;
                foreach ($commands as $index => $command) {
                    $processDefinition = new Definition(Process::class, [$command['definition']]);
                    $processManagerDefinition->addMethodCall('addProcess', [$processDefinition]);

                    if ($index === 0) {
                        $linearPipeComponent = new Definition(
                            LinearPipeComponent::class,
                            [LinearPipeComponent::class, $loggerReference, $processDefinition]
                        );

                        $linearPipeComponent->setFactory([PipeComponentFactory::class, 'create']);

                        $this->createOutputDefinition($command, $linearPipeComponent);

                        $pipeComponents[$id][] = $linearPipeComponent;
                    } elseif ($index === 1) {
                        $teeCommandDefinition = $container->getDefinition('shell_command.commands.tee');
                        $teeProcessDefinition = new Definition(Process::class, [$teeCommandDefinition]);
                        $processManagerDefinition->addMethodCall('addProcess', [$teeProcessDefinition]);

                        $teePipeComponent = new Definition(
                            TeePipeComponent::class,
                            [TeePipeComponent::class, $loggerReference, $teeProcessDefinition]
                        );

                        $teePipeComponent->setFactory([PipeComponentFactory::class, 'create']);

                        $pipeComponents[$id][] = $teePipeComponent;
                    }

                    if ($index >= 1) {
                        $outputDefinition = $this->createOutputDefinition($command);

                        $teePipeComponent->addMethodCall('addFileProcess', [$processDefinition, $outputDefinition]);
                    }
                }
            }

            $pipeDefinition = new Definition(
                Pipe::class,
                [
                    $pipeComponents,
                    $processManagerDefinition,
                    $loggerReference,
                    new Definition(PipeConnector::class)
                ]
            );

            $pipeDefinition->setFactory([PipeFactory::class, 'createPipe']);
            $container->setDefinition(sprintf('shell_command.pipes.%s', $pipeName), $pipeDefinition);
        }
    }

    protected function createOutputDefinition(array $command, Definition $pipeComponent = null): ?Definition
    {
        if (!empty($command['output'])) {
            $accessType = $command['output']['accessType'] ?? File::ACCESS_TYPE_WRITE;
            $outputDefinition = new Definition(File::class, []);
            $outputDefinition->addMethodCall('setAccessType', [$accessType]);
            $outputDefinition->addMethodCall('setResource', [$command['output']['path']]);

            if ($pipeComponent) {
                $pipeComponent->addMethodCall('setOutput', [$outputDefinition]);
            }

            return $outputDefinition;
        }

        return null;
    }
}
