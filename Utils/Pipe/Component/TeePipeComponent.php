<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Commands\Command;
use Shell\Output\OutputHandler;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class TeePipeComponent extends LinearPipeComponent
{
    /** @var Process */
    protected $teeProcess;

    /**
     * @var Process[]
     */
    protected $fileProcesses;

    public function exec(): PipeComponentInterface
    {

    }

    public function addFileProcess(Process $process): PipeComponentInterface
    {
        $this->fileProcesses[] = $process;

        return $this;
    }

    public function getTeeProcess(): Process
    {
        return $this->teeProcess;
    }

    public function setTeeProcess(Process $teeProcess): TeePipeComponentInterface
    {
        $this->teeProcess = $teeProcess;
        return $this;
    }
}
