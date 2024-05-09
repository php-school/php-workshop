<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class ExerciseRunnerEventTest extends TestCase
{
    public function testGetters(): void
    {
        $context = TestContext::withoutDirectories();

        $event = new ExerciseRunnerEvent('Some Event', $context, ['number' => 1]);
        self::assertSame($context, $event->getContext());
        self::assertSame($context->getExercise(), $event->getExercise());
        self::assertSame($context->getInput(), $event->getInput());
        self::assertEquals(
            [
                'exercise' => $context->getExercise(),
                'input' => $context->getInput(),
                'number' => 1
            ],
            $event->getParameters()
        );
    }
}
