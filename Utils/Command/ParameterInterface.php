<?php

namespace Check24\ShellCommandBundle\Utils\Command;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
interface ParameterInterface
{
    public function addParameter(string $key, string $value);
    public function setParameters(array $parameters);
    public function getParameters(): array;
}
