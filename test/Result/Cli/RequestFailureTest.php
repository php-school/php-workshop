<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RequestFailureTest extends TestCase
{
    public function setUp(): void
    {
        $args = new ArrayObject();
        $failure = new RequestFailure($args, 'Expected Output', 'Actual Output');
        $this->assertSame('Request Failure', $failure->getCheckName());
        $this->assertSame($args, $failure->getArgs());
    }

    public function testGetters(): void
    {
        $args = new ArrayObject();
        $failure = new RequestFailure($args, 'Expected Output', 'Actual Output');
        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
        $this->assertSame($args, $failure->getArgs());
    }

    public function testFailureFromArgsAndOutput(): void
    {
        $args = new ArrayObject();
        $failure = RequestFailure::fromArgsAndOutput($args, 'Expected Output', 'Actual Output');
        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
        $this->assertSame($args, $failure->getArgs());
    }
}
