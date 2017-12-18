<?php

namespace Shopping\ShellCommandBundle\Utils;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Shell\Process;

class ProcessManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Process[]
     */
    protected $processes = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

    /**
     *
     */
    public function waitAllProcesses()
    {
        $processes = $this->processes;
        krsort($processes);

        foreach ($processes as $process) {
            try {
                $process->wait();
            } catch (\Exception $e) {
                $this->killAllProcesses();

                $this->logger->error(sprintf('Error: %s', $e->getMessage()));

                throw $e;
            }
        }

        $this->processes = [];
    }
}
