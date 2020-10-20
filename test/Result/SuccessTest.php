<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\Success;

class SuccessTest extends TestCase
{
    public function testSuccess(): void
    {
        $success = new Success('Some Check');
        $this->assertInstanceOf(ResultInterface::class, $success);
        $this->assertEquals('Some Check', $success->getCheckName());
    }

    public function testSuccessFromCheck(): void
    {
        $check = $this->createMock(CheckInterface::class);
        $check
            ->method('getName')
            ->willReturn('Some Check');

        $success = Success::fromCheck($check);
        $this->assertInstanceOf(ResultInterface::class, $success);
        $this->assertEquals('Some Check', $success->getCheckName());
    }
}
