<?php

namespace Shopping\ShellCommandBundle;

use Shopping\ShellCommandBundle\DependencyInjection\ShellCommandExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShellCommandBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShellCommandExtension();
    }
}
