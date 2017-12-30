<?php

namespace Check24\ShellCommandBundle\Utils\Pipe;

use Check24\ShellCommandBundle\Utils\Command\ParameterInterface;
use Check24\ShellCommandBundle\Utils\Command\ParameterTrait;
use Check24\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Check24\ShellCommandBundle\Utils\Pipe\Component\PipeComponentFactory;
use Check24\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Check24\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
use Check24\ShellCommandBundle\Utils\Pipe\Resource\File;
use Check24\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Check24\ShellCommandBundle\Utils\ProcessManager;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Commands\CommandInterface;
use Shell\Process;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class Pipe implements ParameterInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use ParameterTrait;
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /** @var  PipeComponentInterface[] */
    protected $components;

    /** @var  CommandInterface */
    protected $commands;

    /** @var  CommandInterface */
    protected $teeCommand;

    /** @var  array */
    protected $commandOutputs;

    /** @var  ProcessManager */
    protected $processManager;

    /** @var  PipeConnector */
    protected $pipeConnector;

    public function exec(): array
    {
        $this->pipeConnector  = new PipeConnector();
        $this->processManager = new ProcessManager();
        $this->processManager->setLogger($this->logger);

        $this->buildPipe();
        $this->execComponents();

        return $this->processManager->waitAllProcesses();
    }

    protected function buildPipe(): void
    {
        foreach ($this->commands as $id => $commands) {
            foreach ($commands as $index => $command) {
                $process = $this->createProcess($command['definition']);

                if ($index === 0) {
                    $linearPipeComponent = PipeComponentFactory::create(
                        LinearPipeComponent::class,
                        $this->logger,
                        $process,
                        $command['exitCodes']
                    );
                    $this->createOutput($command, $linearPipeComponent);
                    $this->pipeConnector->extendPipe($linearPipeComponent);
                    $this->components[$id][] = $linearPipeComponent;
                } elseif ($index === 1) {
                    $teeProcess = $this->createProcess($this->teeCommand);

                    /** @var TeePipeComponent $teePipeComponent */
                    $teePipeComponent = PipeComponentFactory::create(
                        TeePipeComponent::class,
                        $this->logger,
                        $teeProcess,
                        $command['exitCodes']
                    );
                    $this->pipeConnector->extendPipe($teePipeComponent);
                    $this->components[$id][] = $teePipeComponent;
                }

                if ($index >= 1) {
                    $output = $this->createOutput($command);
                    $teePipeComponent->addFileProcess($process, $output);
                }
            }
        }
    }

    protected function execComponents(): void
    {
        foreach ($this->pipeConnector->getConnectedPipeComponents() as $component) {
            $component->passParameters($this->getParameters());
            $component->exec();
        }
    }

    protected function createOutput(array $command, PipeComponentInterface $pipeComponent = null): ?ResourceInterface
    {
        if (!empty($command['output'])) {
            $accessType = $command['output']['accessType'] ?? File::ACCESS_TYPE_WRITE;
            $output = new File();
            $output->setAccessType($accessType);
            $output->setResource($command['output']['path']);

            if ($pipeComponent) {
                $pipeComponent->setOutput($output);
            }

            return $output;
        }

        return null;
    }

    protected function createProcess(CommandInterface $command): Process
    {
        $process = new Process($command);
        $this->processManager->addProcess($process);
        return $process;
    }

    /**
     * @param CommandInterface $teeCommand
     */
    public function setTeeCommand(CommandInterface $teeCommand)
    {
        $this->teeCommand = $teeCommand;
    }

    public function setCommands(array $commands): Pipe
    {
        $this->commands = $commands;
        return $this;
    }
}
