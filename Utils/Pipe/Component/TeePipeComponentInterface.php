<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Component;

use Shell\Process;
use Shopping\ShellCommandBundle\Utils\Pipe\Resource\ResourceInterface;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
interface TeePipeComponentInterface extends LinearPipeComponentInterface
{
    public function addFileProcess(Process $process, ResourceInterface $output): PipeComponentInterface;
    public function getFileProcesses(): array;
}
