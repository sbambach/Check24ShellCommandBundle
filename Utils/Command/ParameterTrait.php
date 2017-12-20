<?php

namespace Shopping\ShellCommandBundle\Utils\Command;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
trait ParameterTrait
{
    protected $parametersToParse = [];

    public function addParameter(string $key, string $value)
    {
        $this->parametersToParse[$key] = $value;
        return $this;
    }

    public function setParameters(array $parameters)
    {
        $this->parametersToParse = $parameters;
        return $this;
    }

    public function getParameters(): array
    {
        return $this->parametersToParse;
    }
}
