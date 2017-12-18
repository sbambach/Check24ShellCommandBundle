<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\ProcessManager;

class PipeFactory
{
    public function createPipe($pipeName, $commands, $processManager, LoggerInterface $logger)
    {
        $pipe = new Pipe();
        $pipe->setCommands($commands);
        $pipe->setName($pipeName);
        $pipe->setProcessManager($processManager);
        $pipe->setLogger($logger);
        return $pipe;
    }
}
