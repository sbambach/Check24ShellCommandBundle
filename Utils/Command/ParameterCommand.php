<?php

namespace Shopping\ShellCommandBundle\Utils\Command;

use Shell\Commands\Command;
use Shell\Commands\CommandInterface;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class ParameterCommand extends Command implements ParameterInterface
{
    use ParameterTrait;

    public function serialize()
    {
        $serializedCommand = parent::serialize();

        return preg_replace_callback('/\$\{(.*)\}/', function ($result) {
            if (isset($this->parametersToParse[$result[1]])) {
                return $this->parametersToParse[$result[1]];
            }
        }, $serializedCommand);
    }
}
