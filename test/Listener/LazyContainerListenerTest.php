<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ContainerListenerHelper;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Listener\LazyContainerListener;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class LazyContainerListenerTest extends TestCase
{
    public function testExceptionIsThrownIfServiceMethodDoesNotExist(): void
    {
        $myListener = new class {
            public function __invoke()
            {
            }
        };

        $class = get_class($myListener);

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(sprintf('Method "myMethod" does not exist on "%s"', $class));

        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->with('my-listener')
            ->willReturn($myListener);

        $lazy = new LazyContainerListener(
            $container,
            new ContainerListenerHelper('my-listener', 'myMethod')
        );

        $lazy->__invoke(new Event('some-event'));
    }

    public function testThatUnderlyingListenerIsCalled(): void
    {
        $myListener = new class {
            public $called = false;
            public function __invoke()
            {
                $this->called = true;
            }
        };

        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->with('my-listener')
            ->willReturn($myListener);

        $lazy = new LazyContainerListener(
            $container,
            new ContainerListenerHelper('my-listener')
        );

        $lazy->__invoke(new Event('some-event'));

        self::assertTrue($myListener->called);
    }

    public function testWrappedReturnsUnderlyingListener(): void
    {
        $myListener = new class {
            public function __invoke()
            {
            }
        };

        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())
            ->method('get')
            ->with('my-listener')
            ->willReturn($myListener);

        $lazy = new LazyContainerListener(
            $container,
            new ContainerListenerHelper('my-listener', '__invoke')
        );

        $wrapped = $lazy->getWrapped();
        self::assertIsArray($wrapped);
        self::assertEquals($myListener, $wrapped[0]);
        self::assertEquals('__invoke', $wrapped[1]);
    }
}
