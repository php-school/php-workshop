<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Command\RunCommand;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class RunCommandTest extends TestCase
{
    public function test(): void
    {
        $input = new Input('appName', ['program' => 'solution.php']);

        $exercise = new CliExerciseImpl();
        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('my-exercise');
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $dispatcher = $this->prophesize(ExerciseDispatcher::class);
        $dispatcher->run($exercise, $input, $output)->shouldBeCalled();

        $command = new RunCommand($repo, $dispatcher->reveal(), $state, $output);
        $command->__invoke($input);
    }
}
