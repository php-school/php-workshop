<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PDO;
use PhpSchool\PhpWorkshop\Check\CheckRepository;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
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

    public function testSuccessIsReturnedIfDatabaseVerificationPassed()
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution.php'));
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $this->exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CLI()));

        $this->exercise
            ->expects($this->once())
            ->method('configure')
            ->will($this->returnCallback(function (ExerciseDispatcher $dispatcher) {
                $dispatcher->requireListenableCheck(DatabaseCheck::class);
            }));

        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnValue(true));

        $results            = new ResultAggregator;
        $eventDispatcher    = new EventDispatcher($results);
        $checkRepository    = new CheckRepository([$this->check]);
        $dispatcher         = new ExerciseDispatcher(new RunnerFactory, $results, $eventDispatcher, $checkRepository);

        $dispatcher->verify($this->exercise, __DIR__ . '/../res/database/user.php');

        $this->assertTrue($results->isSuccessful());
    }

    public function testFailureIsReturnedIfDatabaseVerificationFails()
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution.php'));
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $this->exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CLI()));

        $this->exercise
            ->expects($this->once())
            ->method('configure')
            ->will($this->returnCallback(function (ExerciseDispatcher $dispatcher) {
                $dispatcher->requireListenableCheck(DatabaseCheck::class);
            }));

        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnValue(false));

        $results            = new ResultAggregator;
        $eventDispatcher    = new EventDispatcher($results);
        $checkRepository    = new CheckRepository([$this->check]);
        $dispatcher         = new ExerciseDispatcher(new RunnerFactory, $results, $eventDispatcher, $checkRepository);

        $dispatcher->verify($this->exercise, __DIR__ . '/../res/database/user.php');

        $this->assertFalse($results->isSuccessful());
        $results = iterator_to_array($results);
        $this->assertSame('Database verification failed', $results[1]->getReason());
    }

    public function testAlteringDatabaseInSolutionDoesNotEffectDatabaseInUserSolution()
    {
        $solution = SingleFileSolution::fromFile(realpath(__DIR__ . '/../res/database/solution-alter-db.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue($solution));

        $this->exercise
            ->expects($this->any())
            ->method('getArgs')
            ->will($this->returnValue([]));

        $this->exercise
            ->expects($this->atLeastOnce())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CLI()));

        $this->exercise
            ->expects($this->once())
            ->method('configure')
            ->will($this->returnCallback(function (ExerciseDispatcher $dispatcher) {
                $dispatcher->requireListenableCheck(DatabaseCheck::class);
            }));

        $this->exercise
            ->expects($this->once())
            ->method('seed')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnCallback(function (PDO $db) {
                $db->exec(
                    'CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT, age INTEGER, gender TEXT)'
                );
                $stmt = $db->prepare('INSERT into users (name, age, gender) VALUES (:name, :age, :gender)');
                $stmt->execute([':name' => 'Jimi Hendrix', ':age' => 27, ':gender' => 'Male']);
            }));

        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnCallback(function (PDO $db) {
                $users = $db->query('SELECT * FROM users');
                $users = $users->fetchAll(PDO::FETCH_ASSOC);

                $this->assertEquals(
                    [
                        ['id' => 1, 'name' => 'Jimi Hendrix', 'age' => '27', 'gender' => 'Male'],
                        ['id' => 2, 'name' => 'Kurt Cobain', 'age' => '27', 'gender' => 'Male'],
                    ],
                    $users
                );
            }));

        $results            = new ResultAggregator;
        $eventDispatcher    = new EventDispatcher($results);
        $checkRepository    = new CheckRepository([$this->check]);
        $dispatcher         = new ExerciseDispatcher(new RunnerFactory, $results, $eventDispatcher, $checkRepository);

        $dispatcher->verify($this->exercise, __DIR__ . '/../res/database/user-solution-alter-db.php');
    }
}
