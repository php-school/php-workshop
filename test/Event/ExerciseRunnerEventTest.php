<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\ExerciseRunnerEvent;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRunnerEventTest extends TestCase
{
    public function testGetters()
    {
        $exercise = new CliExerciseImpl;
        $input = new Input('app');

        $event = new ExerciseRunnerEvent('Some Event', $exercise, $input, ['number' => 1]);
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
