<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\SolutionExecutionException;

class SolutionExecutionExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new SolutionExecutionException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }
}
