<?php

namespace PhpSchool\PhpWorkshopTest\Factory;

use Interop\Container\ContainerInterface;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\ExtRunner;
use PhpSchool\PhpWorkshop\Factory\RunnerFactory;
use PhpSchool\PhpWorkshop\ResultAggregator;
use PhpSchool\PhpWorkshopTest\Asset\AbstractExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseMissingInterface;
use PhpSchool\PhpWorkshopTest\Asset\ExtExerciseImpl;
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
        $type = $this->createMock(ExerciseType::class);
        $type
            ->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnValue('invalid'));

        $exercise = $this->createMock(ExerciseInterface::class);
        $exercise
            ->expects($this->exactly(2))
            ->method('getType')
            ->will($this->returnValue($type));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise Type: "invalid" not supported');

        (new RunnerFactory)
            ->create(
                $exercise,
                new EventDispatcher(new ResultAggregator),
                $this->createMock(ExerciseDispatcher::class)
            );
    }

    public function testExceptionIsThrownIfExerciseDoesNotImplementCorrectInterfaceForItsType()
    {
        $exercise = new CliExerciseMissingInterface;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                '"%s" is required to implement "%s", but it does not',
                CliExerciseMissingInterface::class,
                CliExercise::class
            )
        );

        (new RunnerFactory)
            ->create(
                $exercise,
                new EventDispatcher(new ResultAggregator),
                $this->createMock(ExerciseDispatcher::class)
            );
    }

    public function testAllRunnersCanBeCreated()
    {
        $cliType = new ExerciseType(ExerciseType::CLI);
        $cgiType = new ExerciseType(ExerciseType::CGI);
        $extType = new ExerciseType(ExerciseType::EXT);

        $cliExercise = $this->createMock(CliExerciseInterface::class);
        $cliExercise
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($cliType));

        $cgiExercise = $this->createMock(CgiExerciseInterface::class);
        $cgiExercise
            ->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($cgiType));

        $runnerFactory = new RunnerFactory;
        $eventDispatcher = new EventDispatcher(new ResultAggregator);
        $this->assertInstanceOf(
            CliRunner::class,
            $runnerFactory->create($cliExercise, $eventDispatcher, $this->createMock(ExerciseDispatcher::class))
        );
        $this->assertInstanceOf(
            CgiRunner::class,
            $runnerFactory->create($cgiExercise, $eventDispatcher, $this->createMock(ExerciseDispatcher::class))
        );
        $this->assertInstanceOf(
            ExtRunner::class,
            $runnerFactory->create(new ExtExerciseImpl, $eventDispatcher, $this->createMock(ExerciseDispatcher::class))
        );
    }
}
