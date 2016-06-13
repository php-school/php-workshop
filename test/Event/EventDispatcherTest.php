<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Event\EventInterface;
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
        $mockCallback1 = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mockCallback1->expects($this->exactly(2))
            ->method('__invoke')
            ->with($e)
            ->will($this->returnValue(true));

        $mockCallback2 = $this->getMockBuilder('stdClass')
            ->setMethods(['doNotInvokeMe'])
            ->getMock();

        $mockCallback2->expects($this->never())
            ->method('doNotInvokeMe');

        $cb = function (Event $e) use ($mockCallback1) {
            $mockCallback1($e);
        };

        $this->eventDispatcher->listen('some-event', $cb);
        $this->eventDispatcher->listen('some-event', $cb);
        $this->eventDispatcher->listen('different-event', function (Event $e) use ($mockCallback2) {
            $mockCallback2->doNotInvokeMe($e);
        });
        $this->eventDispatcher->dispatch($e);
    }

    public function testOnlyAppropriateVerifiersAreCalledForEvent()
    {
        $e = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $result = $this->createMock(ResultInterface::class);

        $mockCallback1 = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mockCallback1->expects($this->exactly(2))
            ->method('__invoke')
            ->with($e)
            ->will($this->returnValue($result));

        $cb = function (Event $e) use ($mockCallback1) {
            return $mockCallback1($e);
        };

        $this->eventDispatcher->insertVerifier('some-event', $cb);
        $this->eventDispatcher->insertVerifier('some-event', $cb);
        $this->eventDispatcher->dispatch($e);

        $this->assertEquals([$result, $result], iterator_to_array($this->results));
    }

    public function testVerifyReturnIsSkippedIfNotInstanceOfResult()
    {
        $e = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $mockCallback1 = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mockCallback1->expects($this->once())
            ->method('__invoke')
            ->with($e)
            ->will($this->returnValue(null));

        $this->eventDispatcher->insertVerifier('some-event', function (Event $e) use ($mockCallback1) {
            $mockCallback1($e);
        });
        $this->eventDispatcher->dispatch($e);

        $this->assertEquals([], iterator_to_array($this->results));
    }

    public function testListenWithMultipleEvents()
    {
        $e1 = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $e2 = new Event('some-event', ['arg1' => 1, 'arg2' => 2]);
        $mockCallback1 = $this->getMockBuilder('stdClass')
            ->setMethods(['__invoke'])
            ->getMock();

        $mockCallback1->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive([$e1], [$e2])
            ->will($this->returnValue(true));

        $this->eventDispatcher->listen(['some-event', 'second-event'], $mockCallback1);
        $this->eventDispatcher->dispatch($e1);
        $this->eventDispatcher->dispatch($e2);
    }

    public function testListenersAndVerifiersAreCalledInOrderOfAttachment()
    {
        $e1 = new Event('first-event', ['arg1' => 1, 'arg2' => 2]);


        $counter = 0;
        $this->eventDispatcher->insertVerifier('first-event', function (Event $e) use (&$counter) {
            $this->assertEquals(0, $counter);
            $counter++;
        });

        $this->eventDispatcher->listen('first-event', function (Event $e) use (&$counter) {
            $this->assertEquals(1, $counter);
            $counter++;
        });

        $this->eventDispatcher->dispatch($e1);
    }
}
