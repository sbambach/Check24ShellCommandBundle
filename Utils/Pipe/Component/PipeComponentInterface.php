<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
interface PipeComponentInterface
{
    public function exec(): PipeComponentInterface;
    public function setInput(ResourceInterface $resource);
    public function setOutput(ResourceInterface $resource);
    public function getInput(): ResourceInterface;
    public function getOutput(): ResourceInterface;
    public function getStreamProcess(): Process;
    public function setStreamProcess(Process $process);
}
