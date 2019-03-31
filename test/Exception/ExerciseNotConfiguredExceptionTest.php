<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Exception\ExerciseNotConfiguredException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class ExerciseNotConfiguredExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseNotConfiguredExceptionTest extends TestCase
{
    public function testException()
    {
        $e = new ExerciseNotConfiguredException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testMissingImplementsConstructor()
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Some Exercise'));

        $e = ExerciseNotConfiguredException::missingImplements($exercise, 'SomeInterface');
        $this->assertSame('Exercise: "Some Exercise" should implement interface: "SomeInterface"', $e->getMessage());
    }
}
