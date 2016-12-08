<?php

namespace PhpSchool\PhpWorkshopTest\Result\CLi;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\CLi\GenericFailure;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenericFailureTest extends PHPUnit_Framework_TestCase
{
    public function testFailure()
    {
        $args = new ArrayObject;
        $failure = new GenericFailure($args, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertEquals($args, $failure->getArgs());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CLI Program Runner', $failure->getCheckName());
    }

    public function testFailureWithRequestAndReason()
    {
        $args = new ArrayObject;
        $failure = GenericFailure::fromArgsAndReason($args, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertEquals($args, $failure->getArgs());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CLI Program Runner', $failure->getCheckName());
    }

    public function testFailureFromCodeExecutionException()
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
