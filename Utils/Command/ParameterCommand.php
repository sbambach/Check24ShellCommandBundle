<?php

namespace Check24\ShellCommandBundle\Utils\Command;

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

    /**
     * @var string
     */
    protected $name;

    public function serialize()
    {
        $serializedCommand = parent::serialize();

        return $this->replaceParams($serializedCommand);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ParameterCommand
    {
        $this->name = $name;
        return $this;
    }
}
