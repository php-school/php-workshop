<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;

class CliResultTest extends TestCase
{
    public function testName(): void
    {
        $request = new RequestFailure(new ArrayObject(), 'EXPECTED', 'ACTUAL');
        $cliResult = new CliResult([$request]);
        $this->assertSame('CLI Program Runner', $cliResult->getCheckName());
    }

    public function testIsSuccessful(): void
    {
        $request = new RequestFailure(new ArrayObject(), 'EXPECTED', 'ACTUAL');
        $cliResult = new CliResult([$request]);

        $this->assertFalse($cliResult->isSuccessful());

        $cliResult = new CliResult([new Success(new ArrayObject())]);
        $this->assertTrue($cliResult->isSuccessful());

        $cliResult->add($request);
        $this->assertFalse($cliResult->isSuccessful());
    }
}
