<?php

namespace PhpWorkshop\PhpWorkshopTest\Check;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Check\StdOutCheck;
use PhpWorkshop\PhpWorkshop\Exception\SolutionExecutionException;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class StdOutCheckTest
 * @package PhpWorkshop\PhpWorkshopTest
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

        $this->exercise = $this->getMock(ExerciseInterface::class);
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

        $this->setExpectedExceptionRegExp(
            SolutionExecutionException::class,
            "/^PHP Parse error:  syntax error, unexpected end of file, expecting ',' or ';'/"
        );
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

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals('Output did not match. Expected: "6". Received: "10"', $failure->getReason());
    }
}
