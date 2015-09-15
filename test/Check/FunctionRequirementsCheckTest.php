<?php

namespace PhpWorkshop\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpParser\Lexer\Emulative;
use PhpParser\Parser;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class FunctionRequirementsCheckTest
 * @package PhpWorkshop\PhpWorkshopTest\Check
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsCheckTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FunctionRequirementsCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    /**
     * @var Parser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new Parser(new Emulative);
        $this->check = new FunctionRequirementsCheck($this->parser);
        $this->assertFalse($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock([FunctionRequirementsExerciseCheck::class, ExerciseInterface::class]);
    }

    public function testExceptionIsThrownIfNotValidExcercise()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $this->setExpectedException(InvalidArgumentException::class);

        $this->check->check($exercise, '');
    }

    public function testFailureIsReturnedIfCodeCouldNotBeParsed()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->will($this->returnValue(['file']));

        $this->exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->will($this->returnValue([]));

        $file = __DIR__ . '/../res/function-requirements/fail-invalid-code.php';
        $failure = $this->check->check($this->exercise, $file);
        $this->assertInstanceOf(Failure::class, $failure);

        $message = sprintf('File: %s could not be parsed. Error: "Syntax error, unexpected T_ECHO on line 4"', $file);
        $this->assertEquals($message, $failure->getReason());
    }

    public function testFailureIsReturnedIfBannedFunctionsAreUsed()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->will($this->returnValue(['file']));

        $this->exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->will($this->returnValue([]));

        $failure = $this->check->check(
            $this->exercise,
            __DIR__ . '/../res/function-requirements/fail-banned-function.php'
        );
        $this->assertInstanceOf(Failure::class, $failure);

        $message = 'Some functions were used which should not be used in this exercise: Function: "file" on line: "3"';
        $this->assertEquals($message, $failure->getReason());
    }

    public function testFailureIsReturnedIfNotAllRequiredFunctionsHaveBeenUsed()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->will($this->returnValue([]));

        $this->exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->will($this->returnValue(['file_get_contents', 'implode']));

        $failure = $this->check->check(
            $this->exercise,
            __DIR__ . '/../res/function-requirements/fail-banned-function.php'
        );
        $this->assertInstanceOf(Failure::class, $failure);

        $message  = 'Some function requirements were missing. You should use the functions: ';
        $message .= '"file_get_contents", "implode"';
        $this->assertEquals($message, $failure->getReason());
    }

    public function testSuccess()
    {
        $this->exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->will($this->returnValue([]));

        $this->exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->will($this->returnValue(['file_get_contents']));

        $success = $this->check->check($this->exercise, __DIR__ . '/../res/function-requirements/success.php');
        $this->assertInstanceOf(Success::class, $success);
    }
}
