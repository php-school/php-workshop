<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PDO;
use PhpSchool\PhpWorkshop\Check\DatabaseCheck;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshopTest\Asset\DatabaseExercise;
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

    public function setUp()
    {
        $this->check = new DatabaseCheck;
        $this->exercise = $this->getMock(DatabaseExercise::class);
        $this->assertEquals('Database Check', $this->check->getName());
    }

    public function testExceptionIsThrownIfNotValidExercise()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $this->setExpectedException(InvalidArgumentException::class);

        $this->check->check($exercise, '');
    }

    public function testCheckThrowsExceptionIfSolutionFailsExecution()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/std-out/solution-error.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([]));


        $regex  = "/^PHP Code failed to execute\\. Error: \"PHP Parse error:  syntax error, unexpected end of file";
        $regex .= ", expecting ',' or ';'/";
        $this->setExpectedExceptionRegExp(SolutionExecutionException::class, $regex);
        $this->check->check($this->exercise, '');
    }

    public function testSuccessIsReturnedIfSolutionOutputMatchesUserOutput()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/database/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, __DIR__ . '/../res/database/user.php')
        );
    }

    public function testFailureIsReturnedIfUserSolutionFailsToExecute()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/database/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $failure = $this->check->check($this->exercise, __DIR__ . '/../res/database/user-error.php');

        $failureMsg  = "/^PHP Code failed to execute. Error: \"PHP Parse error:  syntax error, ";
        $failureMsg .= "unexpected end of file, expecting ',' or ';'/";

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertRegExp($failureMsg, $failure->getReason());
    }

    public function testFailureIsReturnedIfSolutionOutputDoesNotMatchUserOutput()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/database/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $failure = $this->check->check($this->exercise, __DIR__ . '/../res/database/user-wrong.php');

        $this->assertInstanceOf(StdOutFailure::class, $failure);
        $this->assertEquals('6', $failure->getExpectedOutput());
        $this->assertEquals('10', $failure->getActualOutput());
    }

    public function testFailureIsReturnedIfDatbaseVerificationFails()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/database/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));
        
        $this->exercise
            ->expects($this->once())
            ->method('verify')
            ->with($this->isInstanceOf(PDO::class))
            ->will($this->returnValue(false));

        $this->assertInstanceOf(
            Failure::class,
            $this->check->check($this->exercise, __DIR__ . '/../res/database/user.php')
        );
    }

    public function testIfDatabaseFolderExistsExceptionIsThrown()
    {
        $folder = sprintf(
            '%s/PhpSchool_PhpWorkshop_Check_DatabaseCheck',
            str_replace('\\', '/', realpath(sys_get_temp_dir()))
        );
        
        mkdir($folder);
        
        try {
            $this->check->check($this->exercise, __DIR__ . '/../res/database/user.php');
            $this->fail('Exception was not thrown');
        } catch (RuntimeException $e) {
            $this->assertEquals(sprintf('Database directory: "%s" already exists', $folder), $e->getMessage());
            rmdir($folder);
        }
    }

    public function testAlteringDatabaseInSolutionDoesNotEffectDatabaseInUserSolution()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/database/solution-alter-db.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([]));
        
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
        
        $this->check->check($this->exercise, __DIR__ . '/../res/database/user-solution-alter-db.php');
    }
}
