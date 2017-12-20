<?php

namespace Shopping\ShellCommandBundle;

use Shopping\ShellCommandBundle\DependencyInjection\ShellCommandExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class ShellCommandBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShellCommandExtension();
    }
}
