<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CheckNotApplicableException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PHPUnit_Framework_TestCase;

/**
 * Class CheckNotApplicableExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckNotApplicableExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new CheckNotApplicableException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testFromCheckAndExerciseConstructor()
    {
        $exercise = $this->getMock(ExerciseInterface::class);
        $exercise
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Some Exercise'));

        $exercise
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue(ExerciseType::CLI()));

        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('Some Check'));


        $e = CheckNotApplicableException::fromCheckAndExercise($check, $exercise);

        $msg  = 'Check: "Some Check" cannot process exercise: "Some Exercise" with ';
        $msg .= 'type: "PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner"';
        $this->assertSame($msg, $e->getMessage());
    }
}
