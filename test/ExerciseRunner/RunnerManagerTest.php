<?php

namespace PhpSchool\PhpWorkshopTest\ExerciseRunner;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\ExerciseRunner\Factory\ExerciseRunnerFactoryInterface;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class RunnerManagerTest extends TestCase
{
    public function testConfigureInputCallsCorrectFactory(): void
    {
        $exercise = new CliExerciseImpl();
        $manager  = new RunnerManager();
        $command  = new CommandDefinition('my-command', [], 'var_dump');

        $factory1 = $this->createMock(ExerciseRunnerFactoryInterface::class);
        $factory1->method('supports')->with($exercise)->willReturn(false);
        $factory1->expects($this->never())->method('configureInput')->with($command);

        $factory2 = $this->createMock(ExerciseRunnerFactoryInterface::class);
        $factory2->method('supports')->with($exercise)->willReturn(true);
        $factory2->expects($this->once())->method('configureInput')->with($command);

        $manager->addFactory($factory1);
        $manager->addFactory($factory2);
        $manager->configureInput($exercise, $command);
    }

    public function testGetRunnerCallsCorrectFactory(): void
    {
        $exercise = new CliExerciseImpl();
        $manager  = new RunnerManager();

        $factory1 = $this->createMock(ExerciseRunnerFactoryInterface::class);
        $factory1->method('supports')->with($exercise)->willReturn(false);
        $factory1->expects($this->never())->method('create')->with($exercise);

        $factory2 = $this->createMock(ExerciseRunnerFactoryInterface::class);
        $factory2->method('supports')->with($exercise)->willReturn(true);
        $factory2->expects($this->once())->method('create')->with($exercise);

        $manager->addFactory($factory1);
        $manager->addFactory($factory2);
        $manager->getRunner($exercise);
    }

    public function testExceptionIsThrownWhenConfiguringInputIfNoFactorySupportsExercise(): void
    {
        $exercise = new CliExerciseImpl();
        $manager = new RunnerManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise Type: "CLI" not supported');

        $manager->configureInput($exercise, new CommandDefinition('my-command', [], 'var_dump'));
    }

    public function testExceptionIsThrownWhenGettingRunnerIfNoFactorySupportsExercise(): void
    {
        $exercise = new CliExerciseImpl();
        $manager = new RunnerManager();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise Type: "CLI" not supported');

        $manager->getRunner($exercise);
    }
}
