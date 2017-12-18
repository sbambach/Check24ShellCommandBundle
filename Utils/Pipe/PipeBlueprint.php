<?php


namespace Shopping\ShellCommandBundle\Utils\Pipe;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class PipeBlueprintFactory implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected $teePipeComponents;
    protected $linearPipeComponents;
    protected $componentOrder;


    public function __construct(array $config)
    {
        foreach($config as $pipe) {

        }
    }

}
