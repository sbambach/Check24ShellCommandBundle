<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Psr\Log\LoggerInterface;
use Shell\Process;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class PipeComponentFactory
{
    public static function create(string $class, LoggerInterface $logger, Process $process): PipeComponentInterface
    {
        if (!is_subclass_of($class, PipeComponentInterface::class)) {
            throw new \InvalidArgumentException(sprintf('Cannot create object of unsupported class %s', $class));
        }

        $component = new $class;
        $component->setLogger($logger);
        $component->setStreamProcess($process);

        return $component;
    }
}
