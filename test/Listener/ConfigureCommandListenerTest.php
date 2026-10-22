<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PHPUnit\Framework\Attributes\DataProvider;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Listener\ConfigureCommandListener;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;

class ConfigureCommandListenerTest extends TestCase
{
    #[DataProvider('configurableCommands')]
    public function testInputIsConfiguredForCorrectCommands(string $commandName): void
    {
        $command = new CommandDefinition($commandName, [], function () {});

        $state    = new UserState([], 'Exercise 1');
        $exercise = new CliExerciseImpl('Exercise 1');
        $repo     = new ExerciseRepository([$exercise]);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->expects($this->once())->method('configureInput')->with($exercise, $command);

        $event = new Event('some-event', ['command' => $command]);
        (new ConfigureCommandListener($state, $repo, $runnerManager))->__invoke($event);
    }

    public static function configurableCommands(): array
    {
        return [
            ['verify'],
            ['run'],
        ];
    }

    #[DataProvider('nonConfigurableCommands')]
    public function testInputIsNotConfiguredForCorrectCommands(string $commandName): void
    {
        $command = new CommandDefinition($commandName, [], function () {});

        $state    = new UserState([], 'Exercise 1');
        $exercise = new CliExerciseImpl('Exercise 1');
        $repo     = new ExerciseRepository([$exercise]);

        $runnerManager = $this->createMock(RunnerManager::class);
        $runnerManager->expects($this->never())->method('configureInput')->with($exercise, $command);

        $event = new Event('some-event', ['command' => $command]);
        (new ConfigureCommandListener($state, $repo, $runnerManager))->__invoke($event);
    }

    public static function nonConfigurableCommands(): array
    {
        return [
            ['print'],
            ['help'],
            ['credits'],
            ['menu'],
        ];
    }
}
