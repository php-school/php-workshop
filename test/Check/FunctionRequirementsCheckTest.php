<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use InvalidArgumentException;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\FunctionRequirementsExercise;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\FunctionRequirementsFailure;
use PhpSchool\PhpWorkshop\Result\Success;

class FunctionRequirementsCheckTest extends TestCase
{
    private FunctionRequirementsCheck $check;
    private FunctionRequirementsExercise $exercise;
    private Parser $parser;

    public function setUp(): void
    {
        $parserFactory = new ParserFactory();
        $this->parser = $parserFactory->create(ParserFactory::PREFER_PHP7);
        $this->check = new FunctionRequirementsCheck($this->parser);
        $this->exercise = new FunctionRequirementsExercise();
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('Function Requirements Check', $this->check->getName());
        $this->assertEquals(FunctionRequirementsExerciseCheck::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_AFTER, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testExceptionIsThrownIfNotValidExercise(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $this->expectException(InvalidArgumentException::class);

        $this->check->check(ExecutionContext::fromInputAndExercise(new Input('app'), $exercise));
    }

    public function testFailureIsReturnedIfCodeCouldNotBeParsed(): void
    {
        $file = __DIR__ . '/../res/function-requirements/fail-invalid-code.php';
        $failure = $this->check->check(
            ExecutionContext::fromInputAndExercise(new Input('app', ['program' => $file]), $this->exercise)
        );

        $this->assertInstanceOf(Failure::class, $failure);
        $message = sprintf('File: "%s" could not be parsed. Error: "Syntax error, unexpected T_ECHO on line 4"', $file);
        $this->assertEquals($message, $failure->getReason());
    }

    public function testFailureIsReturnedIfBannedFunctionsAreUsed(): void
    {
        $file = __DIR__ . '/../res/function-requirements/fail-banned-function.php';
        $failure = $this->check->check(
            ExecutionContext::fromInputAndExercise(new Input('app', ['program' => $file]), $this->exercise)
        );

        $this->assertInstanceOf(FunctionRequirementsFailure::class, $failure);
        $this->assertEquals([['function' => 'file', 'line' => 3]], $failure->getBannedFunctions());
        $this->assertEquals([], $failure->getMissingFunctions());
    }

    public function testFailureIsReturnedIfNotAllRequiredFunctionsHaveBeenUsed(): void
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

        $file = __DIR__ . '/../res/function-requirements/fail-banned-function.php';
        $failure = $this->check->check(
            ExecutionContext::fromInputAndExercise(new Input('app', ['program' => $file]), $exercise)
        );

        $this->assertInstanceOf(FunctionRequirementsFailure::class, $failure);
        $this->assertEquals(['file_get_contents', 'implode'], $failure->getMissingFunctions());
        $this->assertEquals([], $failure->getBannedFunctions());
    }

    public function testSuccess(): void
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

        $file = __DIR__ . '/../res/function-requirements/success.php';
        $success = $this->check->check(
            ExecutionContext::fromInputAndExercise(new Input('app', ['program' => $file]), $exercise)
        );

        $this->assertInstanceOf(Success::class, $success);
    }
}
