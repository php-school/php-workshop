<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PHPUnit\Framework\TestCase;

/**
 * Class CheckNotApplicableExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckNotApplicableExceptionTest extends TestCase
{
    public function testException()
    {
        $e = new CheckNotApplicableException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testFromCheckAndExerciseConstructor()
    {
        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Some Exercise');

        $exercise
            ->expects($this->once())
            ->method('getType')
            ->willReturn(ExerciseType::CLI());

        $check = $this->createMock(CheckInterface::class);
        $check
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Some Check');


        $e = CheckNotApplicableException::fromCheckAndExercise($check, $exercise);

        $msg  = 'Check: "Some Check" cannot process exercise: "Some Exercise" with type: "CLI"';
        $this->assertSame($msg, $e->getMessage());
    }
}
