<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe\Resource;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
interface ResourceInterface
{
    const DESC_SPEC_PIPE    = 'pipe';
    const DESC_SPEC_FILE    = 'file';
    const ACCESS_TYPE_READ  = 'r';
    const ACCESS_TYPE_WRITE = 'w';

    public function openResourceHandle();
    public function setResource($resource);
    public function setAccessType(string $accessType): ResourceInterface;
    public function getAccessType(): string;
}
