<?php

namespace PhpSchool\PhpWorkshop\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\ComposerExercise;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Class PrintCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PrintCommandTest extends PHPUnit_Framework_TestCase
{
    public function testExerciseIsPrintedIfAssigned()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        file_put_contents($file, '### Exercise 1');

        $exercise = $this->prophesize(CliExerciseInterface::class);
        $exercise->getProblem()->willReturn($file);
        $exercise->getType()->willReturn(ExerciseType::CLI());
        $exercise->getName()->willReturn('some-exercise');

        $repo = new ExerciseRepository([$exercise->reveal()]);

        $state = new UserState;
        $state->setCurrentExercise('some-exercise');

        $output = $this->createMock(OutputInterface::class);
        $renderer = $this->createMock(MarkdownRenderer::class);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('### Exercise 1')
            ->will($this->returnValue('### Exercise 1'));

        $output
            ->expects($this->once())
            ->method('write')
            ->with('### Exercise 1');

        $command = new PrintCommand('phpschool', $repo, $state, $renderer, $output);
        $command->__invoke();

        unlink($file);
    }
}
