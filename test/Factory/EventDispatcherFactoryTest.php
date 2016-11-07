<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Factory\EventDispatcherFactory;
use PhpSchool\PhpWorkshop\Listener\CodePatchListener;
use PhpSchool\PhpWorkshop\Listener\PrepareSolutionListener;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PHPUnit_Framework_TestCase;

/**
 * Class EventDispatcherFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcherFactoryTest extends PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertSame(
            [
                'verify.start' => [
                    $prepareSolutionListener
                ],
                'run.start' => [
                    $prepareSolutionListener,
                    [$codePatchListener, 'patch'],
                ],
                'verify.pre.execute' => [
                    [$codePatchListener, 'patch'],
                ],
                'verify.post.execute' => [
                    [$codePatchListener, 'revert'],
                ],
                'run.finish' => [
                    [$codePatchListener, 'revert'],
                ],
                'verify.post.check' => [
                    $selfCheckListener
                ]
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }

    public function testConfigEventListenersThrowsExceptionIfEventsNotArray()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue(new \stdClass));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersThrowsExceptionIfEventsListenersNotArray()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => new \stdClass]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "array" Received: "stdClass"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersThrowsExceptionIfEventsListenerNotCallableOrString()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => [new \stdClass]]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Listener must be a callable or a container entry for a callable service.');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersThrowsExceptionIfEventsListenerContainerEntryNotExist()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => ['nonExistingContainerEntry']]));

        $c->expects($this->at(6))
            ->method('has')
            ->with('nonExistingContainerEntry')
            ->will($this->returnValue(false));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Container has no entry named: "nonExistingContainerEntry"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersThrowsExceptionIfEventsListenerContainerEntryNotCallable()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => ['notCallableEntry']]));

        $c->expects($this->at(6))
            ->method('has')
            ->with('notCallableEntry')
            ->will($this->returnValue(true));

        $c->expects($this->at(7))
            ->method('get')
            ->with('notCallableEntry')
            ->will($this->returnValue(null));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected: "callable" Received: "NULL"');

        (new EventDispatcherFactory)->__invoke($c);
    }

    public function testConfigEventListenersWithAnonymousFunction()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $callback = function () {
        };

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => [$callback]]));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertSame(
            [
                'verify.start' => [
                    $prepareSolutionListener
                ],
                'run.start' => [
                    $prepareSolutionListener,
                    [$codePatchListener, 'patch'],
                ],
                'verify.pre.execute' => [
                    [$codePatchListener, 'patch'],
                ],
                'verify.post.execute' => [
                    [$codePatchListener, 'revert'],
                ],
                'run.finish' => [
                    [$codePatchListener, 'revert'],
                ],
                'verify.post.check' => [
                    $selfCheckListener
                ],
                'someEvent' => [
                    $callback
                ]
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }

    public function testConfigEventListenersWithContainerEntry()
    {
        $c = $this->createMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->createMock(CodePatchListener::class);

        $c->expects($this->at(2))
            ->method('get')
            ->with(CodePatchListener::class)
            ->will($this->returnValue($codePatchListener));

        $selfCheckListener = new SelfCheckListener(new ResultAggregator);

        $c->expects($this->at(3))
            ->method('get')
            ->with(SelfCheckListener::class)
            ->will($this->returnValue($selfCheckListener));

        $c->expects($this->at(4))
            ->method('has')
            ->with('eventListeners')
            ->willReturn(true);

        $c->expects($this->at(5))
            ->method('get')
            ->with('eventListeners')
            ->will($this->returnValue([ 'someEvent' => ['containerEntry']]));

        $c->expects($this->at(6))
            ->method('has')
            ->with('containerEntry')
            ->will($this->returnValue(true));

        $callback = function () {
        };

        $c->expects($this->at(7))
            ->method('get')
            ->with('containerEntry')
            ->will($this->returnValue($callback));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);
        $this->assertInstanceOf(EventDispatcher::class, $dispatcher);
        $this->assertSame(
            [
                'verify.start' => [
                    $prepareSolutionListener
                ],
                'run.start' => [
                    $prepareSolutionListener,
                    [$codePatchListener, 'patch'],
                ],
                'verify.pre.execute' => [
                    [$codePatchListener, 'patch'],
                ],
                'verify.post.execute' => [
                    [$codePatchListener, 'revert'],
                ],
                'run.finish' => [
                    [$codePatchListener, 'revert'],
                ],
                'verify.post.check' => [
                    $selfCheckListener
                ],
                'someEvent' => [
                    $callback
                ]
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }
}
