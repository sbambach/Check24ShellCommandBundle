<?php
/**
 * ParameterCommand.php
 *
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */

namespace Shopping\ShellCommandBundle\Utils\Command;

use Shell\Commands\Command;
use Shell\Commands\CommandInterface;

class ParameterCommand extends Command implements ParameterInterface
{
    use ParameterTrait;

    public function appendArg(string $arg)
    {
        array_push($this->args, $arg);

        return $this;
    }

    public function prependArg(string $arg)
    {
        array_unshift($this->args, $arg);

        return $this;
    }

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
