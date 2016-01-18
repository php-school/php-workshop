<?php

namespace PhpSchool\PhpWorkshopTest\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\FileExistsCheck;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;

/**
 * Class FileExistsCheckTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FileExistsCheckTest extends PHPUnit_Framework_TestCase
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

    public function setUp()
    {
        $this->testDir = sprintf('%s/%s', sys_get_temp_dir(), $this->getName());
        mkdir($this->testDir, 0777, true);
        $this->check = new FileExistsCheck;
        $this->exercise = $this->getMock(ExerciseInterface::class);
        $this->assertEquals('File Exists Check', $this->check->getName());
        $this->assertEquals(ExerciseInterface::class, $this->check->getExerciseInterface());

        $this->assertTrue($this->check->canRun(ExerciseType::CGI()));
        $this->assertTrue($this->check->canRun(ExerciseType::CLI()));
    }

    public function testSuccess()
    {
        $file = sprintf('%s/test.txt', $this->testDir);
        touch($file);

        $this->assertInstanceOf(Success::class, $this->check->check($this->exercise, $file));
        unlink($file);
    }

    public function testFailure()
    {
        $file = sprintf('%s/test.txt', $this->testDir);
        $failure = $this->check->check($this->exercise, $file);
        $this->assertInstanceOf(Failure::class, $failure);
        $this->assertEquals(sprintf('File: "%s" does not exist', $file), $failure->getReason());
    }

    public function tearDown()
    {
        rmdir($this->testDir);
    }
}
