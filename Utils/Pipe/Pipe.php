<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\ProcessManager;
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

            if (count($components) > 0) {
                /** @var TeePipeComponentInterface $teeComponent */
                $teeComponent = array_shift($components);
                $this->pipeConnector->extendPipe($teeComponent);
            }
        }

        $this->execComponents();

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

    public function getPipeConnector(): PipeConnector
    {
        return $this->pipeConnector;
    }

    public function setPipeConnector(PipeConnector $pipeConnector): Pipe
    {
        $this->pipeConnector = $pipeConnector;
        return $this;
    }

    protected function execComponents(): void
    {
        foreach ($this->pipeConnector->getConnectedPipeComponents() as $component) {
            $component->replaceCommandParameters($this->getParameters());
            $component->exec();
        }
    }
}
