<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use AydinHassan\CliMdRenderer\CliRenderer;
use Colors\Color;
use PhpSchool\PhpWorkshop\Factory\MarkdownCliRendererFactory;
use Interop\Container\ContainerInterface;
use PhpSchool\Terminal\Terminal;
use PHPUnit\Framework\TestCase;

class CliRendererFactoryTest extends TestCase
{
    public function testFactoryReturnsInstance(): void
    {
        $terminal = $this->createMock(Terminal::class);
        $terminal
            ->expects($this->once())
            ->method('getWidth')
            ->willReturn(10);

        $services = [
            Terminal::class => $terminal,
            Color::class => new Color(),
        ];

        $c = $this->createMock(ContainerInterface::class);
        $c
            ->method('get')
            ->willReturnCallback(function ($service) use ($services) {
                return $services[$service];
            });

        $factory = new MarkdownCliRendererFactory();
        $this->assertInstanceOf(CliRenderer::class, $factory->__invoke($c));
    }
}
