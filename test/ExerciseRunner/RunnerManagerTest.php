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

        $factory1 = $this->prophesize(ExerciseRunnerFactoryInterface::class);
        $factory1->supports($exercise)->willReturn(false);
        $factory1->configureInput($command)->shouldNotBeCalled();

        $factory2 = $this->prophesize(ExerciseRunnerFactoryInterface::class);
        $factory2->supports($exercise)->willReturn(true);
        $factory2->configureInput($command)->shouldBeCalled();

        $manager->addFactory($factory1->reveal());
        $manager->addFactory($factory2->reveal());
        $manager->configureInput($exercise, $command);
    }

    public function testGetRunnerCallsCorrectFactory(): void
    {
        $exercise = new CliExerciseImpl();
        $manager  = new RunnerManager();

        $factory1 = $this->prophesize(ExerciseRunnerFactoryInterface::class);
        $factory1->supports($exercise)->willReturn(false);
        $factory1->create($exercise)->shouldNotBeCalled();

        $factory2 = $this->prophesize(ExerciseRunnerFactoryInterface::class);
        $factory2->supports($exercise)->willReturn(true);
        $factory2->create($exercise)->shouldBeCalled();

        $manager->addFactory($factory1->reveal());
        $manager->addFactory($factory2->reveal());
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
