<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cli;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Cli\GenericFailure;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenericFailureTest extends TestCase
{
    public function testFailure() : void
    {
        $args = new ArrayObject;
        $failure = new GenericFailure($args, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertEquals($args, $failure->getArgs());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CLI Program Runner', $failure->getCheckName());
    }

    public function testFailureWithRequestAndReason() : void
    {
        $args = new ArrayObject;
        $failure = GenericFailure::fromArgsAndReason($args, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertEquals($args, $failure->getArgs());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CLI Program Runner', $failure->getCheckName());
    }

    public function testFailureFromCodeExecutionException() : void
    {
        $args = new ArrayObject;
        $e = new CodeExecutionException('Something went wrong yo');
        $failure = GenericFailure::fromArgsAndCodeExecutionFailure($args, $e);
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertEquals($args, $failure->getArgs());
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('CLI Program Runner', $failure->getCheckName());
    }
}
