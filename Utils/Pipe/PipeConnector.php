<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Shopping\ShellCommandBundle\Utils\Pipe\Component\PipeComponentInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class PipeConnector
{
    /** @var  PipeComponentInterface[] $lastComponent */
    protected $connectedPipeComponents = [];

    public function extendPipe(PipeComponentInterface $pipeComponent)
    {
        $lastComponent = end($this->connectedPipeComponents);

        if (empty($lastComponent)) {
            $pipeComponent->setInput($this->createResource(Stream::class));
        } else {
            $pipeComponent->setInput($lastComponent->getOutput());
        }

        $pipeComponent->setOutput($this->createResource(Stream::class, ResourceInterface::ACCESS_TYPE_WRITE));

        $this->connectedPipeComponents[] = $pipeComponent;
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
