<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Commands\Command;
use Shell\Output\OutputHandler;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\File;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class TeePipeComponent extends LinearPipeComponent implements TeePipeComponentInterface
{
    /**
     * @var Process[]
     */
    protected $fileProcesses;

    public function exec(): PipeComponentInterface
    {
        $inputs = [];
        foreach ($this->fileProcesses as $fileProcess) {
            $command = $fileProcess->getCommand()->serialize();
            $inputs[$command] = new File();
            $inputs[$command]->openResourceHandle();
        }

        $this->getStreamProcess()->getCommand()->setParameters(['filePath' => reset($inputs)->getFileName()]);

        parent::exec();

        foreach ($this->fileProcesses as $fileProcess) {
            $command = $fileProcess->getCommand()->serialize();
            $this->logger->debug('Running command : {command}', ['command' => $command]);

            $output = new Stream();
            $output->setAccessType(ResourceInterface::ACCESS_TYPE_WRITE);

            $this->runProcessAsync(
                $fileProcess,
                reset($inputs)->openResourceHandle(),
                $output->openResourceHandle()
            );

            $output->setResource($fileProcess->getStdout());
        }

        return $this;
    }

    public function addFileProcess(Process $process): PipeComponentInterface
    {
        $this->fileProcesses[] = $process;

        return $this;
    }

    /**
     * @return Process[]
     */
    public function getFileProcesses(): array
    {
        return $this->fileProcesses;
    }
}
