<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Result\ComparisonFailure;
use PHPUnit\Framework\TestCase;

class ComparisonFailureTest extends TestCase
{
    public function testGetters(): void
    {
        $failure = new ComparisonFailure('Name', 'Expected Output', 'Actual Output');
        self::assertSame('Name', $failure->getCheckName());
        self::assertEquals('Expected Output', $failure->getExpectedValue());
        self::assertEquals('Actual Output', $failure->getActualValue());
    }

    public function testFailureFromArgsAndOutput(): void
    {
        $failure = ComparisonFailure::fromNameAndValues('Name', 'Expected Output', 'Actual Output');
        self::assertSame('Name', $failure->getCheckName());
        self::assertEquals('Expected Output', $failure->getExpectedValue());
        self::assertEquals('Actual Output', $failure->getActualValue());
    }
}
