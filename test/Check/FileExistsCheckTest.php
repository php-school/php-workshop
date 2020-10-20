<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Check\SimpleCheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

class FileExistsCheckTest extends TestCase
{
    /**
     * @var string
     */
    private $testDir;

    /**
     * @var FileExistsCheck
     */
    private $check;

    /**
     * @var ExerciseInterface
     */
    private $exercise;

    public function setUp(): void
    {
        $this->testDir = sprintf('%s/%s', sys_get_temp_dir(), $this->getName());
        mkdir($this->testDir, 0777, true);
        $this->check = new FileExistsCheck();
        $this->exercise = $this->createMock(ExerciseInterface::class);
        $this->assertEquals('File Exists Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());
        $this->assertEquals(SimpleCheckInterface::CHECK_BEFORE, $this->check->getPosition());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess(): void
    {
        $file = sprintf('%s/test.txt', $this->testDir);
        touch($file);

        $this->assertInstanceOf(
            Success::class,
            $this->check->check($this->exercise, new Input('app', ['program' => $file]))
        );
        unlink($file);
    }

    public function testFailure(): void
    {
        $file = sprintf('%s/test.txt', $this->testDir);
        $failure = $this->check->check($this->exercise, new Input('app', ['program' => $file]));
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals(sprintf('File: "%s" does not exist', $file), $failure->getReason());
    }

    public function tearDown(): void
    {
        rmdir($this->testDir);
    }
}
