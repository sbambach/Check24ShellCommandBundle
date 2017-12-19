<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class LinearPipeComponent implements LinearPipeComponentInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Process */
    protected $command;

    /** @var  ResourceInterface */
    protected $output;

    /** @var  ResourceInterface */
    protected $input;

    public function exec(): PipeComponentInterface
    {
        $this->logger->debug('Running command : {command}', ['command' => $this->streamProcess->getCommand()->serialize()]);

        $this->runProcessAsync(
            $this->getStreamProcess(),
            $this->input->openResourceHandle(),
            $this->output->openResourceHandle()
        );

        $this->output->setResource($this->getStreamProcess()->getStdout());

        return $this;
    }

    public function getStreamProcess(): Process
    {
        return $this->streamProcess;
    }

    public function setStreamProcess(Process $streamProcess): LinearPipeComponent
    {
        $this->streamProcess = $streamProcess;
        return $this;
    }

    public function setOutput(ResourceInterface $output): LinearPipeComponent
    {
        $this->output = $output;
        return $this;
    }

    public function setInput(ResourceInterface $input): LinearPipeComponent
    {
        $this->input = $input;
        return $this;
    }

    public function getOutput(): ResourceInterface
    {
        return $this->output;
    }

    public function getInput(): ResourceInterface
    {
        return $this->input;
    }

    public function setCommand(array $command): LinearPipeComponent
    {
        $this->command = $command;
        return $this;
    }

    protected function runProcessAsync(Process $process, $input, $output): PipeComponentInterface
    {
        $process
            ->setStdin($input)
            ->setStdout($output)
            ->onError(
                function (Process $process) {
                    throw new ShellCommandRuntimeError(sprintf('Error: %s', $process->getOutputHandler()->readStdErr()));
                }
            )
            ->runAsync(Process::BLOCKING)
        ;

        return $this;
    }
}