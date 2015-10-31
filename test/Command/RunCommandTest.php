<?php


namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshop\Command\RunCommand;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\Output;
use PhpSchool\PhpWorkshop\UserState;
use PHPUnit_Framework_TestCase;

/**
 * Class RunCommandTest
 * @package PhpSchool\PhpWorkshop\Command
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class RunCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Output
     */
    private $output;

    public function setup()
    {
        $this->output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testContruct()
    {
        $exerciseRepository = new ExerciseRepository([]);
        $userState          = new UserState();

        $command = new RunCommand(
            $exerciseRepository,
            $userState,
            $this->output
        );

        $commandProperties = array_map(function (\ReflectionProperty $property) use ($command) {
            $property->setAccessible(true);
            return $property->getValue($command);
        }, (new \ReflectionClass($command))->getProperties());

        $this->assertTrue(in_array($exerciseRepository, $commandProperties));
        $this->assertTrue(in_array($userState, $commandProperties));
        $this->assertTrue(in_array($this->output, $commandProperties));
    }

    public function testRunPrintsErrorIfProgramDoesNotExist()
    {
        $programFile = sprintf('%s/%s/program.php', sys_get_temp_dir(), $this->getName());

        $this->output
            ->expects($this->once())
            ->method('printError')
            ->with(sprintf('Could not verify. File: "%s" does not exist', $programFile));

        $exerciseRepository = new ExerciseRepository([]);
        $userState          = new UserState();

        $command = new RunCommand(
            $exerciseRepository,
            $userState,
            $this->output
        );

        $this->assertSame(1, $command->__invoke('appname', $programFile));
    }

    public function testRunPrintsErrorIfNoExerciseAssigned()
    {
        $file = tempnam(sys_get_temp_dir(), 'pws');
        touch($file);

        $this->output
            ->expects($this->once())
            ->method('printError')
            ->with('No active exercises. Select one from the menu');

        $exerciseRepository = new ExerciseRepository([]);
        $userState          = new UserState();

        $command = new RunCommand(
            $exerciseRepository,
            $userState,
            $this->output
        );

        $this->assertSame(1, $command->__invoke('appname', $file));

        unlink($file);
    }

    public function testRunPrintsTheProgramsOutput()
    {
        $state = new UserState;
        $state->setCurrentExercise('exercise1');

        $this->output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $e = $this->getMock(ExerciseInterface::class);

        $e->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));

        $e->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([]));

        $repo = new ExerciseRepository([$e]);

        $command = new RunCommand(
            $repo,
            $state,
            $this->output
        );

        $file = realpath(__DIR__ . '/../res/commands/run-command-output.php');

        $this->output
            ->expects($this->once())
            ->method('write')
            ->with('Hello World');

        $command->__invoke('appname', $file);
    }

    public function testRunCorrectlyPassesArgs()
    {
        $state = new UserState;
        $state->setCurrentExercise('exercise1');

        $this->output = $this->getMockBuilder(Output::class)
            ->disableOriginalConstructor()
            ->getMock();

        $e = $this->getMock(ExerciseInterface::class);

        $e->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));

        $e->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue(['Hello World']));

        $repo = new ExerciseRepository([$e]);

        $command = new RunCommand(
            $repo,
            $state,
            $this->output
        );

        $file = realpath(__DIR__ . '/../res/commands/run-command-output.php');

        $this->output
            ->expects($this->once())
            ->method('write')
            ->with('Hello World');

        $command->__invoke('appname', $file);
    }
}
