<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\SolutionFileDoesNotExistException;
use PHPUnit\Framework\TestCase;

class SolutionFileDoesNotExistExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e =  SolutionFileDoesNotExistException::fromExpectedFile('some-file.csv');
        $this->assertEquals('File: "some-file.csv" does not exist in solution folder', $e->getMessage());
    }
}
