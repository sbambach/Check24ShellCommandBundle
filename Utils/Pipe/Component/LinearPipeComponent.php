<?php

namespace Check24\ShellCommandBundle\Utils\Pipe\Component;

use Check24\ShellCommandBundle\Utils\Exception\ShellCommandRuntimeError;
use Check24\ShellCommandBundle\Utils\ParameterInterface;
use Check24\ShellCommandBundle\Utils\Pipe\Resource\File;
use Check24\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shell\Process;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class LinearPipeComponent implements PipeComponentInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var Process */
    protected $streamProcess;

    protected $expectedExitCodes;

    /** @var  ResourceInterface */
    protected $output;

    /** @var  ResourceInterface */
    protected $input;

    /** @var bool */
    protected $lastComponentInPipe = true;

    /**
     * @return PipeComponentInterface
     * @throws \Shell\Exceptions\ProcessException
     */
    public function exec(): PipeComponentInterface
    {
        $this->logger->debug(
            'Running command : {command}',
            ['command' => $this->streamProcess->getCommand()->serialize()]
        );

        $this->runProcessAsync(
            $this->getStreamProcess(),
            $this->input->openResourceHandle(),
            $this->output->openResourceHandle()
        );

        if (!$this->output instanceof File) {
            $this->output->setResource($this->getStreamProcess()->getStdout());
        }

        return $this;
    }

    public function passParameters(array $parameters)
    {
        if ($this->getOutput() instanceof ParameterInterface) {
            $this->getOutput()->setParameters($parameters);
        }

        if ($this->getInput() instanceof ParameterInterface) {
            $this->getInput()->setParameters($parameters);
        }

        if ($this->getStreamProcess()->getCommand() instanceof ParameterInterface) {
            $this->getStreamProcess()->getCommand()->setParameters($parameters);
        }

        return $this;
    }

    public function getStreamProcess(): ?Process
    {
        return $this->streamProcess;
    }

    public function setStreamProcess(Process $streamProcess): PipeComponentInterface
    {
        $this->streamProcess = $streamProcess;
        return $this;
    }

    public function setOutput(ResourceInterface $output): PipeComponentInterface
    {
        $this->output = $output;
        return $this;
    }

    public function setInput(ResourceInterface $input): PipeComponentInterface
    {
        $this->input = $input;
        return $this;
    }

    public function getOutput(): ?ResourceInterface
    {
        return $this->output;
    }

    public function getInput(): ?ResourceInterface
    {
        return $this->input;
    }

    public function getExpectedExitCodes(): array
    {
        return $this->expectedExitCodes;
    }

    public function setExpectedExitCodes(array $expectedExitCodes): PipeComponentInterface
    {
        $this->expectedExitCodes = $expectedExitCodes;
        return $this;
    }

    /**
     * @param Process $process
     * @param         $input
     * @param         $output
     *
     * @return PipeComponentInterface
     * @throws \Shell\Exceptions\ProcessException
     */
    protected function runProcessAsync(Process $process, $input, $output): PipeComponentInterface
    {
        $process
            ->setStdin($input)
            ->setStdout($output)
            ->setExpectedExitcodes($this->getExpectedExitCodes())
            ->onError(
                function (Process $process) {
                    if ($process->getExitCode() === -1) {
                        return;
                    }

                    throw new ShellCommandRuntimeError(sprintf(
                        'CMD: %s, Exit-Code: %d, Error: %s',
                        $process->getCommand()->serialize(),
                        $process->getExitCode(),
                        $process->getOutputHandler()->readStdErr()
                    ));
                }
            )
            ->runAsync(!$this->lastComponentInPipe)
        ;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLastComponentInPipe(): bool
    {
        return $this->lastComponentInPipe;
    }

    /**
     * @param bool $lastComponentInPipe
     *
     * @return LinearPipeComponent
     */
    public function setLastComponentInPipe(bool $lastComponentInPipe): PipeComponentInterface
    {
        $this->lastComponentInPipe = $lastComponentInPipe;

        return $this;
    }
}
