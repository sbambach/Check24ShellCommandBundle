<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Resource;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
interface FileInterface extends ResourceInterface
{
    public function getFilename(): string;
}
