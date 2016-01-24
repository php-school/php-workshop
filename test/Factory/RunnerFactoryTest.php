<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PHPUnit_Framework_TestCase;

/**
 * Class RunnerFactoryTest
 * @package PhpSchool\PhpWorkshopTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunnerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfTypeNotSupported()
    {
        $type = $this->getMockBuilder(ExerciseType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $type
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->returnValue('invalid'));

        $exercise = $this->getMock(ExerciseInterface::class);
        $exercise
            ->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue($type));

        $this->setExpectedException(InvalidArgumentException::class, 'Exercise Type: "invalid" not supported');

        (new RunnerFactory)->create($exercise, new EventDispatcher(new ResultAggregator));
    }

    public function testCliAndCgiRunnerCanBeCreated()
    {
        $cliType = new ExerciseType(ExerciseType::CLI);
        $cgiType = new ExerciseType(ExerciseType::CGI);

        $cliExercise = $this->getMock(CliExerciseInterface::class);
        $cliExercise
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($cliType));

        $cgiExercise = $this->getMock(CgiExerciseInterface::class);
        $cgiExercise
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($cgiType));


        $runnerFactory = new RunnerFactory($this->container);
        $eventDispatcher = new EventDispatcher(new ResultAggregator);
        $this->assertInstanceOf(CliRunner::class, $runnerFactory->create($cliExercise, $eventDispatcher));
        $this->assertInstanceOf(CgiRunner::class, $runnerFactory->create($cgiExercise, $eventDispatcher));
    }
}
