<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use Colors\Color;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Output\OutputInterface;
use PhpSchool\PhpWorkshop\Output\StdOutput;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\ResultRenderer\ResultsRenderer;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshop\UserStateSerializer;

/**
 * Class VerifyCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class VerifyCommandTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var CheckInterface
     */
    private $check;
    
    public function setUp()
    {
        $this->check = $this->createMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
    }

    public function testVerifyAddsCompletedExerciseAndReturnsCorrectCodeOnSuccess()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $input = new Input('appName', ['program' => $file]);

        $exercise = new CliExerciseImpl;
        $repo = new ExerciseRepository([$exercise]);

        $state = new UserState;
        $state->setCurrentExercise('my-exercise');
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));
        
        $serializer = $this->createMock(UserStateSerializer::class);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($state);

        $renderer = $this->createMock(ResultsRenderer::class);

        $results = new ResultAggregator;
        $results->add(new Success($this->check));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('verify')
            ->with($exercise, $input)
            ->will($this->returnValue($results));
        
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($results, $exercise, $state, $output);

    
        $command = new VerifyCommand($repo, $dispatcher, $state, $serializer, $output, $renderer);
        $this->assertEquals(0, $command->__invoke($input));
        $this->assertEquals(['my-exercise'], $state->getCompletedExercises());
        unlink($file);
    }

    public function testVerifyDoesNotAddCompletedExerciseAndReturnsCorrectCodeOnFailure()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $input = new Input('appName', ['program' => $file]);

        $exercise = new CliExerciseImpl;
        $repo = new ExerciseRepository([$exercise]);
        $state = new UserState;
        $state->setCurrentExercise('my-exercise');
        $color = new Color;
        $color->setForceStyle(true);
        $output = new StdOutput($color, $this->createMock(TerminalInterface::class));

        $serializer = $this->createMock(UserStateSerializer::class);

        $serializer
            ->expects($this->never())
            ->method('serialize')
            ->with($state);

        $renderer = $this->createMock(ResultsRenderer::class);

        $results = new ResultAggregator;
        $results->add(new Failure($this->check, 'cba'));

        $dispatcher = $this->createMock(ExerciseDispatcher::class);

        $dispatcher
            ->expects($this->once())
            ->method('verify')
            ->with($exercise, $input)
            ->will($this->returnValue($results));

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
