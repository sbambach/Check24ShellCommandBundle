<?php

namespace Check24\ShellCommandBundle\Utils\Pipe;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Check24\ShellCommandBundle\Utils\Command\ParameterInterface;
use Check24\ShellCommandBundle\Utils\Command\ParameterTrait;
use Check24\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Check24\ShellCommandBundle\Utils\Pipe\Component\TeePipeComponentFactory;
use Check24\ShellCommandBundle\Utils\ProcessManager;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class PipeFactory
{
    public static function createPipe($commands, LoggerInterface $logger, $teeCommand)
    {
        $pipe = new Pipe();
        $pipe->setCommands($commands);
        $pipe->setLogger($logger);
        $pipe->setTeeCommand($teeCommand);
        return $pipe;
    }
}
