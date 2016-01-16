<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PDO;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshopTest\Asset\DatabaseExercise;
use PhpSchool\PhpWorkshopTest\Asset\DatabaseExerciseInterface;
use PHPUnit_Framework_TestCase;
use RuntimeException;

/**
 * Class DatabaseCheckTest
 * @package PhpSchool\PhpWorkshopTest\Check
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DatabaseCheckTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DatabaseCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @var string
     */
    private $dbDir;

    public function setUp()
    {
        $this->check = new DatabaseCheck;
        $this->exercise = $this->getMock(DatabaseExerciseInterface::class);
        $this->dbDir = sprintf(
            '%s/PhpSchool_PhpWorkshop_Check_DatabaseCheck',
            str_replace('\\', '/', realpath(sys_get_temp_dir()))
        );

        $this->assertEquals('Database Verification Check', $this->check->getName());
        $this->assertEquals(DatabaseExerciseCheck::class, $this->check->getExerciseInterface());
    }

    public function testIfDatabaseFolderExistsExceptionIsThrown()
    {
        $eventDispatcher = new EventDispatcher(new ResultAggregator);

        @mkdir($this->dbDir);

        try {
            $this->check->attach($eventDispatcher, $this->exercise);
            $this->fail('Exception was not thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals(sprintf('Database directory: "%s" already exists', $this->dbDir), $e->getMessage());
            rmdir($this->dbDir);
        }
    }

    public function testFailureIsReturnedIfDatabaseVerificationFails()
    {
        $results = new ResultAggregator;
        $eventDispatcher = new EventDispatcher($results);
        $this->check->attach($eventDispatcher, $this->exercise);

        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnValue(false));

        $args = new ArrayObject(1, 2, 3);
        $eventDispatcher->dispatch(new Event('verify.start', ['exercise' => $this->exercise]));
        $solutionEvent = $eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.solution-execute.pre', $args));
        $userEvent = $eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.user-execute.pre', $args));
        $eventDispatcher->dispatch(new Event('verify.finish', ['exercise' => $this->exercise]));

        $this->assertNotSame($solutionEvent->getArgs(), $args);
        $this->assertNotSame($userEvent->getArgs(), $args);
        $this->assertEquals(
            [sprintf('sqlite:%s/solution-db.sqlite', $this->dbDir), 1, 2, 3],
            $solutionEvent->getArgs()->getArrayCopy()
        );
        $this->assertEquals(
            [sprintf('sqlite:%s/user-db.sqlite', $this->dbDir), 1, 2, 3],
            $userEvent->getArgs()->getArrayCopy()
        );

        $this->assertCount(1, iterator_to_array($results));
        $this->assertInstanceOf(Failure::class, iterator_to_array($results)[0]);

        $this->assertFileNotExists(sprintf('%s/user-db.sqlite', $this->dbDir));
        $this->assertFileNotExists(sprintf('%s/solution-db.sqlite', $this->dbDir));
    }

    public function testSuccessIsReturnedIfDatabaseVerificationPassed()
    {
        $results = new ResultAggregator;
        $eventDispatcher = new EventDispatcher($results);
        $this->check->attach($eventDispatcher, $this->exercise);

        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnValue(true));

        $args = new ArrayObject(1, 2, 3);
        $eventDispatcher->dispatch(new Event('verify.start', ['exercise' => $this->exercise]));
        $solutionEvent = $eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.solution-execute.pre', $args));
        $userEvent = $eventDispatcher->dispatch(new CliExecuteEvent('cli.verify.user-execute.pre', $args));
        $eventDispatcher->dispatch(new Event('verify.finish', ['exercise' => $this->exercise]));

        $this->assertNotSame($solutionEvent->getArgs(), $args);
        $this->assertNotSame($userEvent->getArgs(), $args);
        $this->assertEquals(
            [sprintf('sqlite:%s/solution-db.sqlite', $this->dbDir), 1, 2, 3],
            $solutionEvent->getArgs()->getArrayCopy()
        );
        $this->assertEquals(
            [sprintf('sqlite:%s/user-db.sqlite', $this->dbDir), 1, 2, 3],
            $userEvent->getArgs()->getArrayCopy()
        );

        $this->assertCount(1, iterator_to_array($results));
        $this->assertInstanceOf(Success::class, iterator_to_array($results)[0]);

        $this->assertFileNotExists(sprintf('%s/user-db.sqlite', $this->dbDir));
        $this->assertFileNotExists(sprintf('%s/solution-db.sqlite', $this->dbDir));
    }
}
