<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Resource;

use Shopping\ShellCommandBundle\Utils\Command\ParameterInterface;
use Shopping\ShellCommandBundle\Utils\Command\ParameterTrait;
use Shopping\ShellCommandBundle\Utils\Exception\IOException;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class File extends Stream implements FileInterface, ParameterInterface
{
    use ParameterTrait;

    public function openResourceHandle()
    {
        if (empty($this->resource)) {
            $this->resource = sys_get_temp_dir() . '/' . uniqid('pipe_', true) . '.fifo';
            if (!posix_mkfifo($this->resource, 0777)) {
                throw new IOException();
            }
        }

        if (!empty($this->parametersToParse)) {
            $this->resource = $this->replaceParams($this->resource);
        }

        if (!file_exists($this->resource)) {
            touch($this->resource);
        }

        return [static::DESC_SPEC_FILE, $this->resource, $this->accessType];
    }

    public function getFilename(): string
    {
        return $this->resource;
    }
}
