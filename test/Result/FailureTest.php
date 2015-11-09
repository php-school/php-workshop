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
    public function testFailure()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $failure = new Failure($check, 'Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureWithReason()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $failure = Failure::withReason($check, 'Something went wrong yo');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureFromCodeExecutionException()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $e = new CodeExecutionException('Something went wrong yo');
        $failure = Failure::codeExecutionFailure($check, $e);
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('Some Check', $failure->getCheckName());
    }

    public function testFailureFromCodeParseException()
    {
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));

        $e = new Error('Something went wrong yo');
        $failure = Failure::codeParseFailure($check, $e, 'exercise.php');
        $this->assertInstanceOf(ResultInterface::class, $failure);
        $this->assertEquals(
            'File: "exercise.php" could not be parsed. Error: "Something went wrong yo on unknown line"',
            $failure->getReason()
        );
        $this->assertEquals('Some Check', $failure->getCheckName());
    }
}
