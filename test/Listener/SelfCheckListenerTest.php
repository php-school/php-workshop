<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Listener\SelfCheckListener;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\SelfCheckExerciseInterface;
use PHPUnit\Framework\TestCase;

class SelfCheckListenerTest extends TestCase
{
    public function testSelfCheck(): void
    {
        $exercise = $this->createMock(SelfCheckExerciseInterface::class);
        $context  = new TestContext($exercise);
        $event    = new ExerciseRunnerEvent('event', $context);

        $success = new Success('Success');
        $exercise
            ->expects($this->once())
            ->method('check')
            ->willReturn($success);

        $results = new ResultAggregator();
        $listener = new SelfCheckListener($results);
        $listener->__invoke($event);

        $this->assertTrue($results->isSuccessful());
        $this->assertCount(1, $results);
    }

    public function testExerciseWithOutSelfCheck(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $context  = new TestContext($exercise);
        $event    = new ExerciseRunnerEvent('event', $context);

        $results = new ResultAggregator();
        $listener = new SelfCheckListener($results);
        $listener->__invoke($event);

        $this->assertTrue($results->isSuccessful());
        $this->assertCount(0, $results);
    }
}
