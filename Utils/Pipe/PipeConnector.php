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
            $pipeComponent->setInput(new Stream());
        } else {
            $pipeComponent->setInput($lastComponent->getOutput());
        }

        if (empty($pipeComponent->getOutput())) {
            $pipeComponent->setOutput((new Stream())->setAccessType(ResourceInterface::ACCESS_TYPE_WRITE));
        }

        $this->connectedPipeComponents[] = $pipeComponent;
    }

    /**
     * @return PipeComponentInterface[]
     */
    public function getConnectedPipeComponents(): array
    {
        return $this->connectedPipeComponents;
    }
}
