<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Result\ComparisonFailure;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComparisonFailureTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $failure = new ComparisonFailure('Name', 'Expected Output', 'Actual Output');
        self::assertSame('Name', $failure->getCheckName());
        self::assertEquals('Expected Output', $failure->getExpectedValue());
        self::assertEquals('Actual Output', $failure->getActualValue());
    }

    public function testFailureFromArgsAndOutput()
    {
        $failure = ComparisonFailure::fromNameAndValues('Name', 'Expected Output', 'Actual Output');
        self::assertSame('Name', $failure->getCheckName());
        self::assertEquals('Expected Output', $failure->getExpectedValue());
        self::assertEquals('Actual Output', $failure->getActualValue());
    }
}
