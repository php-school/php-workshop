<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshopTest\Asset\StdOutExercise;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\StdOutCheck;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class StdOutCheckTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutCheckTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var StdOutCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp()
    {
        $this->check = new StdOutCheck;
        $this->assertFalse($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock(StdOutExercise::class);
        $this->assertEquals('Command Line Program Output Check', $this->check->getName());
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
            ->will($this->returnValue(__DIR__ . '/../res/std-out/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, __DIR__ . '/../res/std-out/user.php')
        );
    }

    public function testFailureIsReturnedIfUserSolutionFailsToExecute()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getSolution')
            ->will($this->returnValue(__DIR__ . '/../res/std-out/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $failure = $this->check->check($this->exercise, __DIR__ . '/../res/std-out/user-error.php');

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
            ->will($this->returnValue(__DIR__ . '/../res/std-out/solution.php'));

        $this->exercise
            ->expects($this->once())
            ->method('getArgs')
            ->will($this->returnValue([1, 2, 3]));

        $failure = $this->check->check($this->exercise, __DIR__ . '/../res/std-out/user-wrong.php');

        $this->assertInstanceOf(StdOutFailure::class, $failure);
        $this->assertEquals('6', $failure->getExpectedOutput());
        $this->assertEquals('10', $failure->getActualOutput());
    }
}
