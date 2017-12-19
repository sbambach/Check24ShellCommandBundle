<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Resource;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class File extends Stream
{
    public function openResourceHandle()
    {
        if (empty($this->resource)) {
            $this->resource = sys_get_temp_dir() . '/' . mt_rand(10000000, 99999999);
            posix_mkfifo($this->resource, 0777);
        }

        return ['file', $this->resource, $this->accessType];
    }

    public function getFileName()
    {
        return $this->resource;
    }
}
