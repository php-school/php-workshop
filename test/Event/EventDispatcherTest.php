<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PHPUnit_Framework_TestCase;
use stdClass;

/**
 * Class EventDispatcherTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function setUp()
    {
        $this->eventDispatcher = new EventDispatcher;
    }

    public function testOnlyAppropriateListenersAreCalledForEvent()
    {
        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->exactly(2))
            ->method('callback')
            ->with('arg1', 'arg2')
            ->will($this->returnValue(true));

        $mockCallback2 = $this->getMock('stdClass', ['doNotInvokeMe']);
        $mockCallback2->expects($this->never())
            ->method('doNotInvokeMe');

        $this->eventDispatcher->listen('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->listen('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->listen('different-event', [$mockCallback2, 'doNotInvokeMe']);
        $this->eventDispatcher->dispatch('some-event', ['arg1', 'arg2']);
    }

    public function testOnlyAppropriateVerifiersAreCalledForEvent()
    {
        $result = $this->getMock(ResultInterface::class);

        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->exactly(2))
            ->method('callback')
            ->with('arg1', 'arg2')
            ->will($this->returnValue($result));

        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->dispatch('some-event', ['arg1', 'arg2']);

        $this->assertEquals([$result, $result], $this->eventDispatcher->getResults());
    }

    public function testVerifyReturnIsSkippedIfNotInstanceOfResult()
    {
        $mockCallback1 = $this->getMock('stdClass', ['callback']);
        $mockCallback1->expects($this->once())
            ->method('callback')
            ->with('arg1', 'arg2')
            ->will($this->returnValue(null));

        $this->eventDispatcher->insertVerifier('some-event', [$mockCallback1, 'callback']);
        $this->eventDispatcher->dispatch('some-event', ['arg1', 'arg2']);

        $this->assertEquals([], $this->eventDispatcher->getResults());
    }
}
