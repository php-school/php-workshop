<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\FileComparisonCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exception\SolutionFileDoesNotExistException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FileComparisonExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\TestContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use PhpSchool\PhpWorkshopTest\Asset\FileComparisonExercise;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PHPUnit\Framework\TestCase;

class FileComparisonCheckTest extends TestCase
{
    private FileComparisonCheck $check;

    public function setUp(): void
    {
        $this->check = new FileComparisonCheck();
    }

    public function testCheckMeta(): void
    {
        $this->assertEquals('File Comparison Check', $this->check->getName());
        $this->assertEquals(FileComparisonExerciseCheck::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_AFTER, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testExceptionIsThrownIfReferenceFileDoesNotExist(): void
    {
        $this->expectException(SolutionFileDoesNotExistException::class);
        $this->expectExceptionMessage('File: "some-file.txt" does not exist in solution folder');

        $exercise = new FileComparisonExercise(['some-file.txt']);
        $context = TestContext::withDirectories(null, $exercise);

        $this->check->check($context);
    }

    public function testFailureIsReturnedIfStudentsFileDoesNotExist(): void
    {
        $exercise = new FileComparisonExercise(['some-file.txt']);
        $context = TestContext::withDirectories(null, $exercise);
        $context->importReferenceFileFromString("name,age\nAydin,33\nMichael,29\n", 'some-file.txt');

        $failure = $this->check->check($context);

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals('File: "some-file.txt" does not exist', $failure->getReason());
    }

    public function testFailureIsReturnedIfStudentFileDosNotMatchReferenceFile(): void
    {
        $exercise = new FileComparisonExercise(['some-file.txt']);
        $context = TestContext::withDirectories(null, $exercise);
        $context->importStudentFileFromString("somegibberish", 'some-file.txt');
        $context->importReferenceFileFromString("name,age\nAydin,33\nMichael,29\n", 'some-file.txt');

        $failure = $this->check->check($context);

        $this->assertInstanceOf(FileComparisonFailure::class, $failure);
        $this->assertEquals($failure->getFileName(), 'some-file.txt');
        $this->assertEquals($failure->getExpectedValue(), "name,age\nAydin,33\nMichael,29\n");
        $this->assertEquals($failure->getActualValue(), "somegibberish");
    }

    public function testSuccessIsReturnedIfFilesMatch(): void
    {
        $exercise = new FileComparisonExercise(['some-file.txt']);
        $context = TestContext::withDirectories(null, $exercise);

        $context->importStudentFileFromString("name,age\nAydin,33\nMichael,29\n", 'some-file.txt');
        $context->importReferenceFileFromString("name,age\nAydin,33\nMichael,29\n", 'some-file.txt');

        $this->assertInstanceOf(Success::class, $this->check->check($context));
    }

    public function testFailureIsReturnedIfFileDoNotMatchUsingStrip(): void
    {
        $exercise = new FileComparisonExercise(['some-file.txt' => ['strip' => '/\d{2}:\d{2}/']]);
        $context = TestContext::withDirectories(null, $exercise);

        $context->importStudentFileFromString("01:04name,age\n06:76Aydin,34\n99:00Michael,29\n", 'some-file.txt');
        $context->importReferenceFileFromString("01:03name,age\n04:05Aydin,33\n17:21Michael,29\n", 'some-file.txt');

        $failure = $this->check->check($context);

        $this->assertInstanceOf(FileComparisonFailure::class, $failure);
        $this->assertEquals($failure->getFileName(), 'some-file.txt');
        $this->assertEquals($failure->getExpectedValue(), "01:03name,age\n04:05Aydin,33\n17:21Michael,29\n");
        $this->assertEquals($failure->getActualValue(), "01:04name,age\n06:76Aydin,34\n99:00Michael,29\n");
    }
}
