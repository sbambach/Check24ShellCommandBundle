<?php

use Check24\ShellCommandBundle\Utils\Pipe\Component\LinearPipeComponent;
use Check24\ShellCommandBundle\Utils\Pipe\PipeConnector;
use PHPUnit\Framework\TestCase;

/**
 * @author    Eugen Ganshorn <eugen.ganshorn@check24.de>
 * @copyright 2017 CHECK24 Vergleichsportal Shopping GmbH <http://preisvergleich.check24.de>
 */
class PipeConnectorTest extends TestCase
{
    /** @var  PipeConnector */
    protected $sut;

    public function setUp()
    {
        $this->sut = new PipeConnector();
    }

    public function testConnectedPipeComponentsShouldBeEmpty()
    {
        $this->assertSame([], $this->sut->getConnectedPipeComponents());
    }

    public function testExtendPipe()
    {
        $pipeComponent1 = $this->createMock(LinearPipeComponent::class);

        $pipeComponent1
            ->expects($this->once())
            ->method('setInput')
        ;

        $pipeComponent1
            ->expects($this->exactly(2))
            ->method('setOutput')
        ;

        $this->sut->extendPipe($pipeComponent1);

        $pipeComponent2 = clone $pipeComponent1;

        $this->sut->extendPipe($pipeComponent2);
    }
}
