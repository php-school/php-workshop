<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshop\Command\PrintCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Markdown\Renderer;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;

class PrintCommandTest extends TestCase
{
    public function testExerciseIsPrintedIfAssigned(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        file_put_contents($file, '### Exercise 1');

        $exercise = $this->createMock(CliExerciseInterface::class);
        $exercise
            ->method('getProblem')
            ->willReturn($file);

        $exercise
            ->method('getType')
            ->willReturn(ExerciseType::CLI());

        $exercise
            ->method('getName')
            ->willReturn('some-exercise');

        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('some-exercise');

        $output = $this->createMock(OutputInterface::class);
        $renderer = $this->createMock(Renderer::class);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('### Exercise 1')
            ->willReturn('### Exercise 1');

        $output
            ->expects($this->once())
            ->method('write')
            ->with('### Exercise 1');

        $command = new PrintCommand('phpschool', $repo, $state, $renderer, $output);
        $command->__invoke();

        unlink($file);
    }
}
