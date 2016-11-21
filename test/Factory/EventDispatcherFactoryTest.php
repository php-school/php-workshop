<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use DI\ContainerBuilder;
use PhpSchool\PhpWorkshop\Event\Event;
use function PhpSchool\PhpWorkshop\Event\containerListener;
use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PHPUnit_Framework_TestCase;

/**
 * Class EventDispatcherFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcherFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreateWithNoConfig()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertSame([], $this->readAttribute($dispatcher, 'listeners'));
    }

    public function testExceptionIsThrownIfEventListenerGroupsNotArray()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue(new \stdClass));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsNotArray()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue(['my-group' => new \stdClass]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testExceptionIsThrownIfEventListenersNotArray()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([
                'my-group' => [
                    'someEvent' => new \stdClass
                ]
            ]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testExceptionIsThrownIfListenerNotCallable()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([
                'my-group' => [
                    'someEvent' => [new \stdClass]
                ]
            ]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Listener must be a callable or a container entry for a callable service.');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testExceptionIsThrownIfEventsListenerContainerEntryNotExist()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([
                'my-group' => [
                    'someEvent' => [containerListener('nonExistingContainerEntry')]
                ]
            ]));

        $c->expects($this->at(3))
            ->method('has')
            ->with('nonExistingContainerEntry')
            ->will($this->returnValue(false));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container has no entry named: "nonExistingContainerEntry"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersWithAnonymousFunction()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $callback = function () {
        };

        $c->expects($this->at(1))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(2))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([
                'my-group' => [
                    'someEvent' => [$callback]
                ]
            ]));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertSame(
            [
                'someEvent' => [
                    $callback
                ]
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }

    public function testListenerFromContainerIsNotFetchedDuringAttaching()
    {
        $c = $this->prophesize(ContainerInterface::class);

        $c->get(ResultAggregator::class)->willReturn(new ResultAggregator);
        $c->has('eventListeners')->willReturn(true);
        $c->get('eventListeners')->willReturn([
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')]
            ]
        ]);
        $c->has('containerEntry')->willReturn(true);


        $dispatcher = (new EventDispatcherFactory)->__invoke($c->reveal());
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertArrayHasKey('someEvent', $this->readAttribute($dispatcher, 'listeners'));

        $c->get('containerEntry')->shouldNotHaveBeenCalled();
    }

    public function testListenerFromContainerIsFetchedWhenEventDispatched()
    {
        $c = $this->prophesize(ContainerInterface::class);

        $c->get(ResultAggregator::class)->willReturn(new ResultAggregator);
        $c->has('eventListeners')->willReturn(true);
        $c->get('eventListeners')->willReturn([
            'my-group' => [
                'someEvent' => [containerListener('containerEntry')]
            ]
        ]);
        $c->has('containerEntry')->willReturn(true);
        $c->get('containerEntry')->willReturn(function () {
        });

        $dispatcher = (new EventDispatcherFactory)->__invoke($c->reveal());
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertArrayHasKey('someEvent', $this->readAttribute($dispatcher, 'listeners'));

        $dispatcher->dispatch(new Event('someEvent'));
    }

    public function testExceptionIsThrownIfMethodDoesNotExistOnContainerEntry()
    {
        $c = $this->prophesize(ContainerInterface::class);

        $c->get(ResultAggregator::class)->willReturn(new ResultAggregator);
        $c->has('eventListeners')->willReturn(true);
        $c->get('eventListeners')->willReturn([
            'my-group' => [
                'someEvent' => [containerListener('containerEntry', 'notHere')]
            ]
        ]);
        $c->has('containerEntry')->willReturn(true);
        $c->get('containerEntry')->willReturn(new \stdClass);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method "notHere" does not exist on "stdClass"');

        $dispatcher = (new EventDispatcherFactory)->__invoke($c->reveal());
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);

        $dispatcher->dispatch(new Event('someEvent'));
    }

    public function testDefaultListenersAreRegisteredFromConfig()
    {
        $containerBuilder = new ContainerBuilder;
        $containerBuilder->addDefinitions(__DIR__ . '/../../app/config.php');

        $container = $containerBuilder->build();

        $dispatcher = (new EventDispatcherFactory)->__invoke($container);

        $listeners = $this->readAttribute($dispatcher, 'listeners');

        $this->assertArrayHasKey('verify.start', $listeners);
        $this->assertArrayHasKey('run.start', $listeners);
        $this->assertArrayHasKey('verify.pre.execute', $listeners);
        $this->assertArrayHasKey('verify.post.execute', $listeners);
        $this->assertArrayHasKey('run.finish', $listeners);
        $this->assertArrayHasKey('verify.post.check', $listeners);
    }
}
