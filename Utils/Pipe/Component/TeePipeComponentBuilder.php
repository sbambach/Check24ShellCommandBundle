<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class TeePipeComponentBuilder implements ContainerAwareInterface, LoggerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    public function build(LinearPipeComponentInterface $linearComponent, $logger, $process)
    {
        $teeComponent = new TeePipeComponent();
        $teeComponent->setStreamProcess($process);
        $teeComponent->setLogger($logger);

        return $teeComponent;
    }
}
