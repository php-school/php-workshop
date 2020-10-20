<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PHPUnit\Framework\TestCase;

class ExerciseNotConfiguredExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new ExerciseNotConfiguredException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testMissingImplementsConstructor(): void
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Some Exercise');

        $e = ExerciseNotConfiguredException::missingImplements($exercise, 'SomeInterface');
        $this->assertSame('Exercise: "Some Exercise" should implement interface: "SomeInterface"', $e->getMessage());
    }
}
