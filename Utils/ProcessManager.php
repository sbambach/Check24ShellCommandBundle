<?php

namespace Shopping\ShellCommandBundle\Utils;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterCommand;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class ProcessManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Process[]
     */
    protected $processes = [];

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    /**
     * @param Process $process
     */
    public function addProcess(Process $process)
    {
        $this->processes[] = $process;
    }

    /**
     * @param Process|NULL $excludeProcess
     */
    public function killAllProcesses(Process $excludeProcess = null)
    {
        foreach ($this->processes as $process) {
            if ($process === $excludeProcess) {
                continue;
            }

            $process->kill();
        }
    }

    public function waitAllProcesses()
    {
        $processes = $this->processes;
        krsort($processes);

        $results = [];
        foreach ($processes as $process) {
            try {
                $process->wait();

                $command = $process->getCommand();
                if ($command instanceof ParameterCommand) {
                    $name = $command->getName();
                } else {
                    $name = $command->serialize();
                }

                $outputHandler = $process->getOutputHandler();

                $results[$name] = [
                    Process::STDOUT => $outputHandler->readStdOut(),
                    Process::STDERR => $outputHandler->readStdErr(),
                ];
            } catch (\Exception $e) {
                $this->killAllProcesses();

                $this->logger->error(sprintf('Error: %s', $e->getMessage()));

                throw $e;
            }
        }

        $this->processes = [];

        return $results;
    }
}
