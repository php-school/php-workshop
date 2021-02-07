<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PHPUnit\Framework\TestCase;

class FileComparisonFailureTest extends TestCase
{
    public function testGetters(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->method('getName')
            ->willReturn('Some Check');

        $failure = new FileComparisonFailure($check, 'users.txt', 'Expected Output', 'Actual Output');
        $this->assertEquals('Expected Output', $failure->getExpectedValue());
        $this->assertEquals('Actual Output', $failure->getActualValue());
        $this->assertEquals('users.txt', $failure->getFileName());
    }
}
