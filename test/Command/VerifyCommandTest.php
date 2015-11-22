<?php

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Command\VerifyCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Output;
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
        $this->check = $this->getMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
    }

    public function testVerifyPrintsErrorIfProgramDoesNotExist()
    {
        $repo = new ExerciseRepository([]);
        $state = new UserState;
        $output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $runner = $this->getMockBuilder(ExerciseRunner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $programFile = sprintf('%s/%s/program.php', sys_get_temp_dir(), $this->getName());
        $output
            ->expects($this->once())
            ->method('printError')
            ->with(sprintf('Could not verify. File: "%s" does not exist', $programFile));

        $serializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(ResultsRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $command = new VerifyCommand($repo, $runner, $state, $serializer, $output, $renderer);
        $this->assertSame(1, $command->__invoke('appname', $programFile));
    }

    public function testVerifyPrintsErrorIfNoExerciseAssigned()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $repo = new ExerciseRepository([]);
        $state = new UserState;
        $output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $runner = $this->getMockBuilder(ExerciseRunner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $output
            ->expects($this->once())
            ->method('printError')
            ->with('No active exercises. Select one from the menu');

        $serializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(ResultsRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $command = new VerifyCommand($repo, $runner, $state, $serializer, $output, $renderer);
        $this->assertSame(1, $command->__invoke('appname', $file));

        unlink($file);
    }

    public function testVerifyAddsCompletedExerciseAndReturnsCorrectCodeOnSuccess()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $e = $this->getMock(ExerciseInterface::class);
        $e->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));
        $repo = new ExerciseRepository([$e]);
        $state = new UserState;
        $state->setCurrentExercise('exercise1');
        $output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($state);

        $renderer = $this->getMockBuilder(ResultsRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $results = new ResultAggregator;
        $results->add(new Success($this->check));

        $runner = $this->getMockBuilder(ExerciseRunner::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $runner
            ->expects($this->once())
            ->method('runExercise')
            ->with($e, $file)
            ->will($this->returnValue($results));
        
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($results)
            ->will($this->returnValue('RESULT OUTPUT'));
        
        $output
            ->expects($this->once())
            ->method('write')
            ->with('RESULT OUTPUT');
    
        $command = new VerifyCommand($repo, $runner, $state, $serializer, $output, $renderer);
        $this->assertEquals(0, $command->__invoke('appname', $file));
        $this->assertEquals(['exercise1'], $state->getCompletedExercises());
        unlink($file);
    }

    public function testVerifyDoesNotAddCompletedExerciseAndReturnsCorrectCodeOnFailure()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $e = $this->getMock(ExerciseInterface::class);
        $e->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));
        $repo = new ExerciseRepository([$e]);
        $state = new UserState;
        $state->setCurrentExercise('exercise1');
        $output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer = $this->getMockBuilder(UserStateSerializer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer
            ->expects($this->never())
            ->method('serialize')
            ->with($state);

        $renderer = $this->getMockBuilder(ResultsRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $results = new ResultAggregator;
        $results->add(new Failure($this->check, 'cba'));

        $runner = $this->getMockBuilder(ExerciseRunner::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $runner
            ->expects($this->once())
            ->method('runExercise')
            ->with($e, $file)
            ->will($this->returnValue($results));

        $renderer
            ->expects($this->once())
            ->method('render')
            ->with($results)
            ->will($this->returnValue('RESULT OUTPUT'));

        $output
            ->expects($this->once())
            ->method('write')
            ->with('RESULT OUTPUT');

        $command = new VerifyCommand($repo, $runner, $state, $serializer, $output, $renderer);
        $this->assertEquals(1, $command->__invoke('appname', $file));
        $this->assertEquals([], $state->getCompletedExercises());
        unlink($file);
    }
}
