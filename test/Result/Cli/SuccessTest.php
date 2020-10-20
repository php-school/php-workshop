<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;

class SuccessTest extends TestCase
{
    public function testSuccess(): void
    {
        $args = new ArrayObject();
        $success = new Success($args);
        $this->assertInstanceOf(Success::class, $success);
        $this->assertSame($args, $success->getArgs());
        $this->assertEquals('CLI Program Runner', $success->getCheckName());
    }
}
