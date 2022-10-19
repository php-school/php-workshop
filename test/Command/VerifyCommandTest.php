<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\PhpWorkshop\UserState\Serializer;
use PhpSchool\Terminal\Terminal;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState\UserState;

class VerifyCommandTest extends TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp(): void
    {
        $this->check = $this->createMock(CheckInterface::class);
        $this->check
            ->method('getName')
            ->willReturn('Some Check');
    }

    public function testVerifyAddsCompletedExerciseAndReturnsCorrectCodeOnSuccess(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $input = new Input('appName', ['program' => $file]);

        $exercise = new CliExerciseImpl();
        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState();
        $state->setCurrentExercise('my-exercise');
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $serializer = $this->createMock(Serializer::class);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($state);

        $renderer = $this->createMock(ResultsRenderer::class);

        $results = new ResultAggregator();
        $results->add(Success::fromCheck($this->check));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('verify')
            ->with($exercise, $input)
            ->willReturn($results);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($results, $exercise, $state, $output);


        $command = new VerifyCommand($repo, $dispatcher, $state, $serializer, $output, $renderer);
        $this->assertEquals(0, $command->__invoke($input));
        $this->assertEquals(['my-exercise'], $state->getCompletedExercises());
        unlink($file);
    }

    public function testVerifyDoesNotAddCompletedExerciseAndReturnsCorrectCodeOnFailure(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $input = new Input('appName', ['program' => $file]);

        $exercise = new CliExerciseImpl();
        $repo = new ExerciseRepository([$exercise]);
        $state = new UserState();
        $state->setCurrentExercise('my-exercise');
        $color = new Color();
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(Terminal::class));

        $serializer = $this->createMock(Serializer::class);

        $serializer
            ->expects($this->never())
            ->method('serialize')
            ->with($state);

        $renderer = $this->createMock(ResultsRenderer::class);

        $results = new ResultAggregator();
        $results->add(Failure::fromCheckAndReason($this->check, 'cba'));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('verify')
            ->with($exercise, $input)
            ->willReturn($results);

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($results, $exercise, $state, $output);

        $command = new VerifyCommand($repo, $dispatcher, $state, $serializer, $output, $renderer);
        $this->assertEquals(1, $command->__invoke($input));
        $this->assertEquals([], $state->getCompletedExercises());
        unlink($file);
    }
}
