<?php

namespace Check24\ShellCommandBundle\Utils\Pipe\Resource;

use Check24\ShellCommandBundle\Utils\Exception\IOException;
use Check24\ShellCommandBundle\Utils\ParameterInterface;
use Check24\ShellCommandBundle\Utils\ParameterTrait;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class File extends Stream implements ParameterInterface
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
            (new Filesystem())->mkdir(dirname($this->resource));
            touch($this->resource);
        }

        return [static::DESC_SPEC_FILE, $this->resource, $this->accessType];
    }

    public function getFilename(): string
    {
        return $this->resource;
    }
}
