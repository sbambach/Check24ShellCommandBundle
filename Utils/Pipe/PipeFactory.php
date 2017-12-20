<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentFactory;
use Shopping\ShellCommandBundle\Utils\ProcessManager;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class PipeFactory
{
    public static function createPipe($pipeName, $components, ProcessManager $processManager, LoggerInterface $logger, PipeConnector $pipeConnector)
    {
        $pipe = new Pipe();
        $pipe->setComponents($components);
        $pipe->setName($pipeName);
        $pipe->setProcessManager($processManager);
        $pipe->setLogger($logger);
        $pipe->setPipeConnector($pipeConnector);
        return $pipe;
    }
}
