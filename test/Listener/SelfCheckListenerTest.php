<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
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
        $input    = new Input('app', ['program' => 'some-file.php']);
        $context  = new CliContext(ExecutionContext::fromInputAndExercise($input, $exercise));
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
        $input    = new Input('app', ['program' => 'some-file.php']);
        $event    = new ExerciseRunnerEvent(
            'event',
            new CliContext(ExecutionContext::fromInputAndExercise($input, $exercise))
        );

        $results = new ResultAggregator();
        $listener = new SelfCheckListener($results);
        $listener->__invoke($event);

        $this->assertTrue($results->isSuccessful());
        $this->assertCount(0, $results);
    }
}
