<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentBuilder;
use Shopping\ShellCommandBundle\Utils\ProcessManager;

class PipeFactory
{
    public static function createPipe($pipeName, $components, $processManager, LoggerInterface $logger)
    {
        $pipe = new Pipe();
        $pipe->setComponents($components);
        $pipe->setName($pipeName);
        $pipe->setProcessManager($processManager);
        $pipe->setLogger($logger);
        return $pipe;
    }
}
