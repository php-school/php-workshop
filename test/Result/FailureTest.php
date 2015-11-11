<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpParser\Error;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Failure;

/**
 * Class FailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FailureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp()
    {
        $this->check = $this->getMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $failure = new Failure($this->check, '');
        $this->assertSame('Some Check', $failure->getCheckName());
    }
    
    public function testFailure()
    {
        $failure = new Failure($this->check, 'Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureWithReason()
    {
        $failure = Failure::withReason($this->check, 'Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureFromCodeExecutionException()
    {
        $e = new CodeExecutionException('Something went wrong yo');
        $failure = Failure::codeExecutionFailure($this->check, $e);
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureFromCodeParseException()
    {
        $e = new Error('Something went wrong yo');
        $failure = Failure::codeParseFailure($this->check, $e, 'exercise.php');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals(
            'File: "exercise.php" could not be parsed. Error: "Something went wrong yo on unknown line"',
            $failure->getReason()
        );
        $this->assertEquals('Some Check', $failure->getCheckName());
    }
}
