<?php


namespace PhpSchool\PhpWorkshopTest;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\StdOutExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseRunner;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Result\Success;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\StdOutExercise;
use stdClass;

/**
 * Class ExerciseRunnerTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRunnerTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterExerciseWithNonStringNonNullThrowsException()
    {
        $runner = new ExerciseRunner;
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Expected a string. Got: "stdClass"'
        );
        $runner->registerCheck($this->getMock(CheckInterface::class), new stdClass);
    }

    public function testRegisterCheck()
    {
        $runner = new ExerciseRunner;
        $runner->registerCheck($this->getMock(CheckInterface::class), 'SomeInterface');
    }

    public function testRunExerciseOnlyRunsRequiredChecks()
    {
        $runner = new ExerciseRunner;
        $doNotRunMe = $this->getMock(CheckInterface::class);
        $runner->registerCheck($doNotRunMe, StdOutExerciseCheck::class);

        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $result = $runner->runExercise($this->getMock(ExerciseInterface::class), 'some-file.php');
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testRunExerciseWithRequiredChecks()
    {
        $runner = new ExerciseRunner;
        $runMe = $this->getMock(CheckInterface::class);
        $runner->registerCheck($runMe, StdOutExerciseCheck::class);

        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $runMe
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Success($check)));

        $result = $runner->runExercise(new StdOutExercise, 'some-file.php');
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertTrue($result->isSuccessful());
    }

    public function testReturnEarly()
    {
        $runner = new ExerciseRunner;
        $runMe = $this->getMock(CheckInterface::class);
        $check = $this->getMock(CheckInterface::class);
        $check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        $runMe
            ->expects($this->once())
            ->method('check')
            ->will($this->returnValue(new Failure($check, 'nope')));

        $runMe
            ->expects($this->once())
            ->method('breakChainOnFailure')
            ->will($this->returnValue(true));

        $doNotRunMe = $this->getMock(CheckInterface::class);
        $doNotRunMe
            ->expects($this->never())
            ->method('check');

        $runner->registerCheck($runMe, StdOutExerciseCheck::class);
        $runner->registerCheck($doNotRunMe, StdOutExerciseCheck::class);

        $result = $runner->runExercise(new StdOutExercise, 'some-file.php');
        $this->assertInstanceOf(ResultAggregator::class, $result);
        $this->assertFalse($result->isSuccessful());
    }
}
