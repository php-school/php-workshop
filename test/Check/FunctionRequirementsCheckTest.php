<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\FunctionRequirementsExercise;
use PHPUnit\Framework\TestCase;
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
class FunctionRequirementsCheckTest extends TestCase
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
        $this->exercise = new FunctionRequirementsExercise;
        $this->assertEquals('Function Requirements Check', $this->check->getName());
        $this->assertEquals(FunctionRequirementsExerciseCheck::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_AFTER, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testExceptionIsThrownIfNotValidExercise()
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $this->expectException(InvalidArgumentException::class);

        $this->check->check($exercise, new Input('app'));
    }

    public function testFailureIsReturnedIfCodeCouldNotBeParsed()
    {
        $file = __DIR__ . '/../res/function-requirements/fail-invalid-code.php';
        $failure = $this->check->check($this->exercise, new Input('app', ['program' => $file]));
        $this->assertInstanceOf(Failure::class, $failure);

        $message = sprintf('File: "%s" could not be parsed. Error: "Syntax error, unexpected T_ECHO on line 4"', $file);
        $this->assertEquals($message, $failure->getReason());
    }

    public function testFailureIsReturnedIfBannedFunctionsAreUsed()
    {
        $failure = $this->check->check(
            $this->exercise,
            new Input('app', ['program' => __DIR__ . '/../res/function-requirements/fail-banned-function.php'])
        );
        $this->assertInstanceOf(FunctionRequirementsFailure::class, $failure);
        $this->assertEquals([['function' => 'file', 'line' => 3]], $failure->getBannedFunctions());
        $this->assertEquals([], $failure->getMissingFunctions());
    }

    public function testFailureIsReturnedIfNotAllRequiredFunctionsHaveBeenUsed()
    {
        $exercise = $this->createMock(FunctionRequirementsExercise::class);
        $exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->willReturn([]);

        $exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->willReturn(['file_get_contents', 'implode']);

        $failure = $this->check->check(
            $exercise,
            new Input('app', ['program' => __DIR__ . '/../res/function-requirements/fail-banned-function.php'])
        );
        $this->assertInstanceOf(FunctionRequirementsFailure::class, $failure);
        
        $this->assertEquals(['file_get_contents', 'implode'], $failure->getMissingFunctions());
        $this->assertEquals([], $failure->getBannedFunctions());
    }

    public function testSuccess()
    {
        $exercise = $this->createMock(FunctionRequirementsExercise::class);
        $exercise
            ->expects($this->once())
            ->method('getBannedFunctions')
            ->willReturn([]);

        $exercise
            ->expects($this->once())
            ->method('getRequiredFunctions')
            ->willReturn(['file_get_contents']);

        $success = $this->check->check(
            $exercise,
            new Input('app', ['program' => __DIR__ . '/../res/function-requirements/success.php'])
        );
        $this->assertInstanceOf(Success::class, $success);
    }
}
