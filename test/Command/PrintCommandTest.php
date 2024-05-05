<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshop\Command\PrintCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\OutputInterface;

class PrintCommandTest extends TestCase
{
    public function testExerciseIsPrintedIfAssigned(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        file_put_contents($file, '### Exercise 1');

        $exercise = new CliExerciseImpl('some-exercise');
        $exercise->setProblem($file);

        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('some-exercise');

        $output = $this->createMock(OutputInterface::class);
        $renderer = $this->createMock(MarkdownRenderer::class);

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
