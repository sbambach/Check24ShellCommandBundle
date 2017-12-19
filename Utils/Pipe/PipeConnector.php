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
use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentBuilder;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;
use Shopping\ShellCommandBundle\Utils\ProcessManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class PipeConnector
{
    /** @var  PipeComponentInterface[] $lastComponent */
    protected $connectedPipeComponents;

    public function extendPipe(PipeComponentInterface $pipeComponent)
    {
        $lastComponent = end($this->connectedPipeComponents);

        if (empty($lastComponent)) {
            $pipeComponent->setInput($this->createResource(Stream::class));
        } else {
            $pipeComponent->setInput($lastComponent->getOutput());
        }
        $pipeComponent->setOutput($this->createResource(Stream::class, ResourceInterface::ACCESS_TYPE_WRITE));

        $this->connectedPipeComponents[$pipeComponent];
    }

    protected function createResource(string $class, string $accessType = ResourceInterface::ACCESS_TYPE_READ): ResourceInterface
    {
        return (new $class())->setAccessType($accessType);
    }

    /**
     * @return PipeComponentInterface[]
     */
    public function getConnectedPipeComponents(): array
    {
        return $this->connectedPipeComponents;
    }
}
