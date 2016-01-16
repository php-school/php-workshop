<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PHPUnit_Framework_TestCase;

/**
 * Class EventDispatcherTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ResultAggregator
     */
    private $results;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp()
    {
        $this->results = new ResultAggregator;
        $this->eventDispatcher = new EventDispatcher($this->results);
    }

    public function testOnlyAppropriateListenersAreCalledForEvent()
    {
        $e = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->exactly(2))
            ->method('callback')
            ->with($e)
            ->will($this->returnValue(true));

        $mockCallback2 = $this->getMock('stdClass', ['doNotInvokeMe']);
        $mockCallback2->expects($this->never())
            ->method('doNotInvokeMe');

        $this->eventDispatcher->listen('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->listen('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->listen('different-event', [$mockCallback2, 'doNotInvokeMe']);
        $this->eventDispatcher->dispatch($e);
    }

    public function testOnlyAppropriateVerifiersAreCalledForEvent()
    {
        $e = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $result = $this->getMock(ResultInterface::class);

        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->exactly(2))
            ->method('callback')
            ->with($e)
            ->will($this->returnValue($result));

        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->dispatch($e);

        $this->assertEquals([$result, $result], iterator_to_array($this->results));
    }

    public function testVerifyReturnIsSkippedIfNotInstanceOfResult()
    {
        $e = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->once())
            ->method('callback')
            ->with($e)
            ->will($this->returnValue(null));

        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->dispatch($e);

        $this->assertEquals([], iterator_to_array($this->results));
    }
}
