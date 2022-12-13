<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Command\RunCommand;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class RunCommandTest extends BaseTest
{
    public function test(): void
    {
        $input = new Input('appName', ['program' => $this->getTemporaryFile('solution.php')]);

        $exercise = new CliExerciseImpl();
        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('my-exercise');
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);
        $dispatcher
            ->expects($this->once())
            ->method('run')
            ->with($exercise, $input, $output);

        $command = new RunCommand($repo, $dispatcher, $state, $output);
        $command->__invoke($input);
    }

    public function testWithNonExistingFile(): void
    {
        $input = new Input('appName', ['program' => 'solution.php']);

        $exercise = new CliExerciseImpl();
        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('my-exercise');
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);
        $dispatcher
            ->expects($this->never())
            ->method('run');

        $this->expectOutputString(file_get_contents(__DIR__ . '/../res/app-run-missing-solution-expected.txt'));

        $command = new RunCommand($repo, $dispatcher, $state, $output);
        $command->__invoke($input);
    }
}
