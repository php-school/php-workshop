<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
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
        $c = $this->getMock(ContainerInterface::class);

        $c->expects($this->at(0))
            ->method('get')
            ->with(ResultAggregator::class)
            ->will($this->returnValue(new ResultAggregator));

        $prepareSolutionListener = new PrepareSolutionListener;

        $c->expects($this->at(1))
            ->method('get')
            ->with(PrepareSolutionListener::class)
            ->will($this->returnValue($prepareSolutionListener));

        $codePatchListener = $this->getMockBuilder(CodePatchListener::class)
            ->disableOriginalConstructor()
            ->getMock();

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
                'verify.pre.execute' => [
                    [$codePatchListener, 'patch'],
                ],
                'verify.post.execute' => [
                    [$codePatchListener, 'revert'],
                ],
                'verify.post.check' => [
                    $selfCheckListener
                ]
            ],
            $this->readAttribute($dispatcher, 'listeners')
        );
    }
}
