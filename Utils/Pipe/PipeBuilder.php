<?php

namespace Shopping\ShellCommandBundle\Utils\Pipe;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Shopping\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponentInterface;

/**
 * @author    Silvester Denk <silvester.denk@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://www.check24.de/>
 */
class PipeBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var  LinearPipeComponentInterface[] */
    protected $pipeComponents;

    public function buildPipe(string $category)
    {
        $pipeStart = array_shift($this->pipeComponents);
        $pipeStart->exec($category);
        $pipe = $pipeStart->getOutput();

        $pipeEnd = array_pop($this->pipeComponents);

        foreach ($this->pipeComponents as $pipeComponent) {

            if($pipeComponent->needsPreviousOutput()) {
                $pipeComponent->setInput($pipe);
            }
        }

    }

}
