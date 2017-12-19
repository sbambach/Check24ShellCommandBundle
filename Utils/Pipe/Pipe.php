<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentBuilder;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;
use Shopping\ShellCommandBundle\Utils\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Pipe implements ParameterInterface, ContainerAwareInterface, LoggerAwareInterface
{
    use ParameterTrait;
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /** @var  string */
    protected $name;

    /** @var  PipeComponentInterface[] */
    protected $components;

    /** @var  ProcessManager */
    protected $processManager;

    /** @var  PipeConnector */
    protected $pipeConnector;

    public function exec()
    {
        foreach ($this->components as $components) {
            /** @var LinearPipeComponentInterface $linearComponent */
            $linearComponent = array_shift($components);
            $this->pipeConnector->extendPipe($linearComponent);
            $process = $linearComponent->getStreamProcess();
            $this->processManager->addProcess($process);

            /** @var LinearPipeComponent */
            $lastComponent = $linearComponent;

            if (count($components) > 0)
                /** @var TeePipeComponentInterface $teeComponent */
                $teeComponent = array_shift($components);
                $teeComponent->exec();
                $process = $teeComponent->getStreamProcess();

                $this->processManager->addProcess($process);

                $processes = $teeComponent->getFileProcesses();
                foreach ($processes as $process) {
                    $this->processManager->addProcess($process);
                }

                /** @var PipeComponentInterface $lastComponent */
                $lastComponent = $teeComponent;
            }
        }

        $this->processManager->waitAllProcesses();
    }

    public function setName(string $name): Pipe
    {
        $this->name = $name;
        return $this;
    }

    public function setComponents(array $components): Pipe
    {
        $this->components = $components;
        return $this;
    }

    public function setProcessManager(ProcessManager $processManager): Pipe
    {
        $this->processManager = $processManager;
        return $this;
    }

    public function getTeePipeComponentBuilder(): TeePipeComponentBuilder
    {
        return $this->teePipeComponentBuilder;
    }

    public function setTeePipeComponentBuilder(TeePipeComponentBuilder $teePipeComponentBuilder): Pipe
    {
        $this->teePipeComponentBuilder = $teePipeComponentBuilder;
        return $this;
    }
}
