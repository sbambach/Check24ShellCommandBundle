<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
interface PipeComponentInterface
{
    public function exec(): PipeComponentInterface;
    public function passParameters(array $parameters);
    public function setInput(ResourceInterface $resource): PipeComponentInterface;
    public function setOutput(ResourceInterface $resource): PipeComponentInterface;
    public function getInput(): ?ResourceInterface;
    public function getOutput(): ?ResourceInterface;
    public function getStreamProcess(): ?Process;
    public function setStreamProcess(Process $process): PipeComponentInterface;
}
