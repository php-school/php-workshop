<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CliExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class CliExerciseRunnerEventTest extends TestCase
{
    public function testGetters(): void
    {
        $context = TestContext::withoutDirectories();
        $scenario = new CliScenario();

        $event = new CliExerciseRunnerEvent('Some Event', $context, $scenario, ['number' => 1]);
        self::assertSame($context->getExercise(), $event->getExercise());
        self::assertSame($context->getInput(), $event->getInput());
        $this->assertSame($context, $event->getContext());
        $this->assertSame($scenario, $event->getScenario());
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
