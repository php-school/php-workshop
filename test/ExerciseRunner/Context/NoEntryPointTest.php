<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\ExerciseRunner\Context\NoEntryPoint;
use PHPUnit\Framework\TestCase;

class NoEntryPointTest extends TestCase
{
    public function testException(): void
    {
        $e = new NoEntryPoint();
        static::assertSame('No entry point provided', $e->getMessage());
    }
}
