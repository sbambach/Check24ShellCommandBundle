<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\File;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\Stream;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class TeePipeComponent extends LinearPipeComponent implements TeePipeComponentInterface
{
    /** @var array */
    protected $fileProcesses;

    public function exec(): PipeComponentInterface
    {
        $filePath = [];
        $inputs   = [];
        foreach ($this->fileProcesses as $fileProcess) {
            $name  = $fileProcess['process']->getCommand()->getName();
            $input = &$inputs[$name];
            $input = new File();
            $input->openResourceHandle();

            $filePath[] = $input->getFilename();
        }
        unset($input);

        $this->getStreamProcess()->getCommand()->addParameter('filePath', implode(' ', $filePath));

        parent::exec();

        foreach ($this->fileProcesses as $fileProcess) {
            $process = $fileProcess['process'];
            $output  = $fileProcess['output'];
            $name    = $process->getCommand()->getName();
            $command = $process->getCommand()->serialize();

            $this->logger->debug('Running command : {command}', ['command' => $command]);

            if (empty($output)) {
                $output = new Stream();
                $output->setAccessType(ResourceInterface::ACCESS_TYPE_WRITE);
            }

            $this->runProcessAsync(
                $process,
                $inputs[$name]->openResourceHandle(),
                $output->openResourceHandle()
            );

            if (!$output instanceof File) {
                $output->setResource($process->getStdout());
            }
        }

        return $this;
    }

    public function addFileProcess(Process $process, ResourceInterface $output = null): PipeComponentInterface
    {
        $this->fileProcesses[] = [
            'process' => $process,
            'output'  => $output,
        ];

        return $this;
    }

    public function passParameters(array $parameters)
    {
        parent::passParameters($parameters);

        foreach ($this->getFileProcesses() as $fileProcess) {
            if ($fileProcess['output'] instanceof ParameterInterface) {
                $fileProcess['output']->setParameters($parameters);
            }
        }

        return $this;
    }

    public function getFileProcesses(): array
    {
        return $this->fileProcesses;
    }
}
