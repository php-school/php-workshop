<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Event\Event;
use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
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
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                new \stdClass()
            );

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsNotArray(): void
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                ['my-group' => new \stdClass()]
            );

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventListenersNotArray(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => new \stdClass()
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig
            );

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfListenerNotCallable(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [new \stdClass()]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig
            );

        $c->method('has')->with('eventListeners')->willReturn(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Listener must be a callable or a container entry for a callable service.');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsListenerContainerEntryNotExist(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('nonExistingContainerEntry')]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig
            );

        $c->method('has')
            ->withConsecutive(
                ['eventListeners'],
                ['nonExistingContainerEntry']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false
            );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container has no entry named: "nonExistingContainerEntry"');

        (new EventDispatcherFactory())->__invoke($c);
    }

    public function testConfigEventListenersWithAnonymousFunction(): void
    {
        $callback = function () {
        };

        $eventConfig = [
            'my-group' => [
                'someEvent' => [$callback]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig
            );

        $c->method('has')->with('eventListeners')->willReturn(true);

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertSame(
            [
                'someEvent' => [
                    $callback
                ]
            ],
            $dispatcher->getListeners()
        );
    }

    public function testListenerFromContainerIsNotFetchedDuringAttaching(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig
            );

        $c->method('has')
            ->withConsecutive(
                ['eventListeners'],
                ['containerEntry']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertArrayHasKey('someEvent', $dispatcher->getListeners());
    }

    public function testListenerFromContainerIsFetchedWhenEventDispatched(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners'],
                ['containerEntry']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig,
                function () {
                }
            );

        $c->method('has')
            ->withConsecutive(
                ['eventListeners'],
                ['containerEntry']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

        $dispatcher = (new EventDispatcherFactory())->__invoke($c);
        $this->assertArrayHasKey('someEvent', $dispatcher->getListeners());

        $dispatcher->dispatch(new Event('someEvent'));
    }

    public function testExceptionIsThrownIfMethodDoesNotExistOnContainerEntry(): void
    {
        $eventConfig = [
            'my-group' => [
                'someEvent' => [containerListener('containerEntry', 'notHere')]
            ]
        ];

        $c = $this->createMock(ContainerInterface::class);

        $c->method('get')
            ->withConsecutive(
                [ResultAggregator::class],
                ['eventListeners'],
                ['containerEntry']
            )
            ->willReturnOnConsecutiveCalls(
                new ResultAggregator(),
                $eventConfig,
                new \stdClass()
            );

        $c->method('has')
            ->withConsecutive(
                ['eventListeners'],
                ['containerEntry'],
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true
            );

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
