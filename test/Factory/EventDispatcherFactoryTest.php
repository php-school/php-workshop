<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Event\Event;
use Psr\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\CallableStub;
use PHPUnit\Framework\TestCase;

use function PhpSchool\PhpWorkshop\Event\containerListener;

class EventDispatcherFactoryTest extends TestCase
{
    public function testCreateWithNoConfig(): void
    {
        $c = $this->createMock(ContainerInterface::class);
        $c->method('get')->with(ResultAggregator::class)->willReturn(new ResultAggregator());
        $c->method('has')->with('eventListeners')->willReturn(false);

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertSame([], $dispatcher->getListeners());
    }

    public function testExceptionIsThrownIfEventListenerGroupsNotArray(): void
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', new \stdClass()],
            ]);

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsNotArray(): void
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', ['my-group' => new \stdClass()]],
            ]);

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventListenersNotArray(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => new \stdClass(),
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
            ]);

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfListenerNotCallable(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [new \stdClass()],
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
            ]);

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Listener must be a callable or a container entry for a callable service.');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsListenerContainerEntryNotExist(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('nonExistingContainerEntry')()],
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
            ]);

        $c->method('has')
            ->willReturnMap([
                ['eventListeners', true],
                ['nonExistingContainerEntry', false],
            ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container has no entry named: "nonExistingContainerEntry"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testConfigEventListenersWithAnonymousFunction(): void
    {
        $callback = function () {};

        $eventConfig = [
            'my-group' => [
                'someEvent' => [$callback],
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
            ]);

        $c->method('has')->with('eventListeners')->willReturn(true);

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertSame(
            [
                'someEvent' => [
                    $callback,
                ],
            ],
            $dispatcher->getListeners(),
        );
    }

    public function testListenerFromContainerIsNotFetchedDuringAttaching(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')()],
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
            ]);

        $c->method('has')
            ->willReturnMap([
                ['eventListeners', true],
                ['containerEntry', true],
            ]);

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertArrayHasKey('someEvent', $dispatcher->getListeners());
    }

    public function testListenerFromContainerIsFetchedWhenEventDispatched(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')()],
            ],
        ];

        $listener = $this->createMock(CallableStub::class);
        $listener->expects($this->once())->method('__invoke');

        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
                ['containerEntry', $listener],
            ]);

        $c->method('has')
            ->willReturnMap([
                ['eventListeners', true],
                ['containerEntry', true],
            ]);

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertArrayHasKey('someEvent', $dispatcher->getListeners());

        $dispatcher->dispatch(new Event('someEvent'));
    }

    public function testExceptionIsThrownIfMethodDoesNotExistOnContainerEntry(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry', 'notHere')()],
            ],
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->willReturnMap([
                [ResultAggregator::class, new ResultAggregator()],
                ['eventListeners', $eventConfig],
                ['containerEntry', new \stdClass()],
            ]);

        $c->method('has')
            ->willReturnMap([
                ['eventListeners', true],
                ['containerEntry', true],
            ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method "notHere" does not exist on "stdClass"');

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);

        $dispatcher->dispatch(new Event('someEvent'));
    }

    public function testDefaultListenersAreRegisteredFromConfig(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/../../app/config.php');

        $container = $containerBuilder->build();

        $dispatcher = (new EventDispatcherFactory())->__invoke($container);

        $listeners = $dispatcher->getListeners();

        $this->assertArrayHasKey('cli.verify.start', $listeners);
        $this->assertArrayHasKey('cli.run.start', $listeners);
        $this->assertArrayHasKey('cgi.verify.start', $listeners);
        $this->assertArrayHasKey('cgi.run.start', $listeners);
        $this->assertArrayHasKey('verify.post.check', $listeners);
    }
}
