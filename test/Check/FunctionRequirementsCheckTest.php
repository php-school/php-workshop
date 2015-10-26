<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class FunctionRequirementsCheckTest
 * @package PhpSchool\PhpWorkshopTest\Check
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
        $parserFactory = new ParserFactory;
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        $this->check = new FunctionRequirementsCheck($this->parser);
        $this->assertFalse($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock([FunctionRequirementsExerciseCheck::class, ExerciseInterface::class]);
    }

    public function testExceptionIsThrownIfNotValidExercise()
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
        $this->assertInstanceOf(FunctionRequirementsFailure::class, $failure);

        $message = 'Function Requirements were not met';
        $this->assertEquals($message, $failure->getReason());
        $this->assertEquals([['function' => 'file', 'line' => 3]], $failure->getBannedFunctions());
        $this->assertEquals([], $failure->getMissingFunctions());
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

        $message  = 'Function Requirements were not met';
        $this->assertEquals($message, $failure->getReason());
        $this->assertEquals(['file_get_contents', 'implode'], $failure->getMissingFunctions());
        $this->assertEquals([], $failure->getBannedFunctions());
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
