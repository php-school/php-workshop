<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\SelfCheckExerciseInterface;
use PHPUnit_Framework_TestCase;

/**
 * Class SelfCheckListenerTest
 * @package PhpSchool\PhpWorkshopTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SelfCheckListenerTest extends PHPUnit_Framework_TestCase
{
    public function testSelfCheck()
    {
        $exercise = $this->getMock(SelfCheckExerciseInterface::class);
        $event = new Event('event', ['exercise' => $exercise, 'fileName' => 'some-file.php']);

        $success = new Success('Success');
        $exercise
            ->expects($this->once())
            ->method('check')
            ->with('some-file.php')
            ->will($this->returnValue($success));

        $results = new ResultAggregator;
        $listener = new SelfCheckListener($results);
        $listener->__invoke($event);

        $this->assertTrue($results->isSuccessful());
        $this->assertCount(1, $results);
    }

    public function testExerciseWithOutSelfCheck()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $event = new Event('event', ['exercise' => $exercise, 'fileName' => 'some-file.php']);
        $exercise
            ->expects($this->never())
            ->method('check');

        $results = new ResultAggregator;
        $listener = new SelfCheckListener($results);
        $listener->__invoke($event);

        $this->assertTrue($results->isSuccessful());
        $this->assertCount(0, $results);
    }
}
