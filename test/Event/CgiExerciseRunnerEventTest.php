<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CgiExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PHPUnit\Framework\TestCase;

class CgiExerciseRunnerEventTest extends TestCase
{
    public function testGetters(): void
    {
        $context = new TestContext();
        $scenario = new CgiScenario();

        $event = new CgiExerciseRunnerEvent('Some Event', $context, $scenario, ['number' => 1]);
        self::assertSame($context->getExercise(), $event->getExercise());
        self::assertSame($context->getInput(), $event->getInput());
        $this->assertSame($context, $event->getContext());
        $this->assertSame($scenario, $event->getScenario());
        self::assertEquals(
            [
                'exercise' => $context->getExercise(),
                'input' => $context->getInput(),
                'number' => 1,
            ],
            $event->getParameters(),
        );
    }
}
