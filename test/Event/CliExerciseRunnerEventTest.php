<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CliExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Exercise\MockExercise;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class CliExerciseRunnerEventTest extends TestCase
{
    public function testGetters(): void
    {
        $exercise = new MockExercise();
        $input = new Input('app');

        $event = new CliExerciseRunnerEvent('Some Event', $exercise, $input, ['number' => 1]);
        self::assertSame($exercise, $event->getExercise());
        self::assertSame($input, $event->getInput());
        self::assertEquals(
            [
                'exercise' => $exercise,
                'input' => $input,
                'number' => 1
            ],
            $event->getParameters()
        );
    }
}
