<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
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
    public function testCreationWithNoCoreListeners()
    {
        $c = $this->getMock(ContainerInterface::class);
        $c->expects($this->once())
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $this->assertInstanceOf(EventDispatcher::class, (new EventDispatcherFactory)->__invoke($c));
    }

    public function testCreationWithCoreListeners()
    {
        $c          = $this->getMock(ContainerInterface::class);
        $listener   = [$this->getMock('stdClass', ['callback']), 'callback'];

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $c->expects($this->at(1))
            ->method('has')
            ->with('coreListeners')
            ->will($this->returnValue(true));

        $c->expects($this->at(2))
            ->method('get')
            ->with('coreListeners')
            ->will($this->returnValue(['some.event' => 'listener']));

        $c->expects($this->at(3))
            ->method('get')
            ->with('listener')
            ->will($this->returnValue($listener));

        $dispatcher = (new EventDispatcherFactory)->__invoke($c);

        $this->assertSame(
            [
                'some.event' => [
                    $listener
                ],
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }
}
