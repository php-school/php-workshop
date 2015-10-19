<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use AydinHassan\CliMdRenderer\CliRenderer;
use Colors\Color;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PHPUnit_Framework_TestCase;

/**
 * Class CliRendererFactoryTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRendererFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactoryReturnsInstance()
    {
        $terminal = $this->getMock(TerminalInterface::class);
        $terminal
            ->expects($this->once())
            ->method('getWidth')
            ->will($this->returnValue(10));

        $services = [
            TerminalInterface::class => $terminal,
            Color::class => new Color,
        ];

        $c = $this->getMock(ContainerInterface::class);
        $c->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($service) use ($services) {
                return $services[$service];
            }));

        $factory = new MarkdownCliRendererFactory();
        $this->assertInstanceOf(CliRenderer::class, $factory->__invoke($c));
    }
}
