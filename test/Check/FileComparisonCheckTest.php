<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\FileComparisonCheck;
use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exception\SolutionFileDoesNotExistException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FileComparisonExerciseCheck;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\Asset\FileComparisonExercise;
use PhpSchool\PhpWorkshopTest\BaseTest;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

class FileComparisonCheckTest extends BaseTest
{
    /**
     * @var FileComparisonCheck
     */
    private $check;

    public function setUp(): void
    {
        $this->check = new FileComparisonCheck();
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
        $exercise->setSolution(new SingleFileSolution($this->getTemporaryFile('solution/solution.php')));

        $this->check->check($exercise, new Input('app', ['program' => 'my-solution.php']));
    }

    public function testFailureIsReturnedIfStudentsFileDoesNotExist(): void
    {
        $referenceFile = $this->getTemporaryFile('solution/some-file.txt', "name,age\nAydin,33\nMichael,29\n");

        $exercise = new FileComparisonExercise(['some-file.txt']);
        $exercise->setSolution(new SingleFileSolution($this->getTemporaryFile('solution/solution.php')));

        $failure = $this->check->check($exercise, new Input('app', ['program' => 'my-solution.php']));

        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals('File: "some-file.txt" does not exist', $failure->getReason());
    }

    public function testFailureIsReturnedIfStudentFileDosNotMatchReferenceFile(): void
    {
        $referenceFile = $this->getTemporaryFile('solution/some-file.txt', "name,age\nAydin,33\nMichael,29\n");
        $studentFile = $this->getTemporaryFile('student/some-file.txt', "somegibberish");

        $exercise = new FileComparisonExercise(['some-file.txt']);
        $exercise->setSolution(new SingleFileSolution($this->getTemporaryFile('solution/solution.php')));

        $failure = $this->check->check($exercise, new Input('app', [
            'program' => $this->getTemporaryFile('student/my-solution.php')
        ]));

        $this->assertInstanceOf(FileComparisonFailure::class, $failure);
        $this->assertEquals($failure->getFileName(), 'some-file.txt');
        $this->assertEquals($failure->getExpectedValue(), "name,age\nAydin,33\nMichael,29\n");
        $this->assertEquals($failure->getActualValue(), "somegibberish");
    }

    public function testSuccessIsReturnedIfFilesMatch(): void
    {
        $referenceFile = $this->getTemporaryFile('solution/some-file.txt', "name,age\nAydin,33\nMichael,29\n");
        $studentFile = $this->getTemporaryFile('student/some-file.txt', "name,age\nAydin,33\nMichael,29\n");

        $exercise = new FileComparisonExercise(['some-file.txt']);
        $exercise->setSolution(new SingleFileSolution($this->getTemporaryFile('solution/solution.php')));

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($exercise, new Input('app', [
                'program' => $this->getTemporaryFile('student/my-solution.php')
            ]))
        );
    }

    public function testFailureIsReturnedIfFileDoNotMatchUsingStrip(): void
    {
        $referenceFile = $this->getTemporaryFile(
            'solution/some-file.txt',
            "01:03name,age\n04:05Aydin,33\n17:21Michael,29\n"
        );
        $studentFile = $this->getTemporaryFile(
            'student/some-file.txt',
            "01:04name,age\n06:76Aydin,34\n99:00Michael,29\n"
        );

        $exercise = new FileComparisonExercise(['some-file.txt' => ['strip' => '/\d{2}:\d{2}/']]);
        $exercise->setSolution(new SingleFileSolution($this->getTemporaryFile('solution/solution.php')));

        $failure = $this->check->check($exercise, new Input('app', [
            'program' => $this->getTemporaryFile('student/my-solution.php')
        ]));

        $this->assertInstanceOf(FileComparisonFailure::class, $failure);
        $this->assertEquals($failure->getFileName(), 'some-file.txt');
        $this->assertEquals($failure->getExpectedValue(), "01:03name,age\n04:05Aydin,33\n17:21Michael,29\n");
        $this->assertEquals($failure->getActualValue(), "01:04name,age\n06:76Aydin,34\n99:00Michael,29\n");
    }
}
