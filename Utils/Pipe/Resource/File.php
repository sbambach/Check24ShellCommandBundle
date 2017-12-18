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
            $this->resource = mt_rand(1, 9);
            posix_mkfifo($this->resource, 0777);
        }

        return fopen($this->resource, $this->accessType);
    }
}
