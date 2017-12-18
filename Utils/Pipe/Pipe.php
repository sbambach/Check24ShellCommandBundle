<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use PHPUnit\TextUI\Command;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shell\Commands\CommandInterface;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
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

    /** @var  ParameterCommand[] */
    protected $commands;

    /** @var  ProcessManager */
    protected $processManager;

    /** @var  PipeComponentFactory */
    protected $componentFactory;

    public function exec()
    {
        $lastComponent = null;
        foreach ($this->commands as $commands) {
            /** @var ParameterCommand $streamProcessCommand */
            $streamProcessCommand = array_shift($commands);
            if (count($commands) == 0) {
                $component = new LinearPipeComponent();
            } else {
                $component = new TeePipeComponent();
                $component->setTeeProcess($this->createProcess(new ParameterCommand('tee')));

                foreach ($commands as $command) {
                    $component->addFileProcess($this->createProcess($command)); // md5sum
                }
            }

            $component->setLogger($this->logger);

            if ($lastComponent === null) {
                $component->setInput($this->createResource(Stream::class));
            } else {
                $component->setInput($lastComponent->getOutput());
            }

            $component->setOutput($this->createResource(Stream::class, ResourceInterface::ACCESS_TYPE_WRITE));

            $component->setException(ShellCommandRuntimeError::class);
            $component->setStreamProcess($this->createProcess($streamProcessCommand)); // wget

            $process = $component
                ->exec()
                ->getStreamProcess()
            ;

            $this->processManager->addProcess($process);

            /** @var LinearPipeComponent */
            $lastComponent = $component;
        }

        $this->processManager->waitAllProcesses();
    }

    protected function createProcess(ParameterCommand $command)
    {
        $command->setParamteters($this->getParameters());
        return Process::make($command);
    }

    protected function createResource(string $class, string $accessType = ResourceInterface::ACCESS_TYPE_READ): ResourceInterface
    {
        return (new $class())->setAccessType($accessType);
    }

    public function setName(string $name): Pipe
    {
        $this->name = $name;
        return $this;
    }

    public function setCommands(array $commands): Pipe
    {
        $this->commands = $commands;
        return $this;
    }

    public function setProcessManager(ProcessManager $processManager): Pipe
    {
        $this->processManager = $processManager;
        return $this;
    }
}
