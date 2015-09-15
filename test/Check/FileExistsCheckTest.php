<?php

namespace PhpWorkshop\PhpWorkshopTest\Check;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Check\FileExistsCheck;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class FileExistsCheckTest
 * @package PhpWorkshop\PhpWorkshopTest
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
        $this->assertTrue($this->check->breakChainOnFailure());

        $this->exercise = $this->getMock(ExerciseInterface::class);
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
