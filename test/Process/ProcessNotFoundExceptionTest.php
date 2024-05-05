<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\ProcessNotFoundException;
use PHPUnit\Framework\TestCase;

class ProcessNotFoundExceptionTest extends TestCase
{
    public function testFromExecutable(): void
    {
        $exception = ProcessNotFoundException::fromExecutable('composer');

        static::assertSame('Could not find executable: "composer"', $exception->getMessage());
    }
}
