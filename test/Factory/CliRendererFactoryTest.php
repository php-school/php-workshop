<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use AydinHassan\CliMdRenderer\CliRenderer;
use Colors\Color;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class CliRendererFactoryTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRendererFactoryTest extends TestCase
{
    public function testFactoryReturnsInstance()
    {
        $terminal = $this->createMock(TerminalInterface::class);
        $terminal
            ->expects($this->once())
            ->method('getWidth')
            ->willReturn(10);

        $services = [
            TerminalInterface::class => $terminal,
            Color::class => new Color,
        ];

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->method('get')
            ->willReturnCallback(function ($service) use ($services) {
                return $services[$service];
            }
            );

        $factory = new MarkdownCliRendererFactory();
        $this->assertInstanceOf(CliRenderer::class, $factory->__invoke($c));
    }
}
