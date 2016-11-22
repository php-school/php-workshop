<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\ExerciseRepository;
use PhpSchool\PhpWorkshop\ExerciseRunner\RunnerManager;
use PhpSchool\PhpWorkshop\Listener\ConfigureCommandListener;
use PhpSchool\PhpWorkshop\UserState;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ConfigureCommandListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configurableCommands
     * @param string $commandName
     */
    public function testInputIsConfiguredForCorrectCommands($commandName)
    {
        $command = new CommandDefinition($commandName, [], function () {
        });

        $state    = new UserState([], 'Exercise 1');
        $exercise = new CliExerciseImpl('Exercise 1');
        $repo     = new ExerciseRepository([$exercise]);

        $runnerManager = $this->prophesize(RunnerManager::class);
        $runnerManager->configureInput($exercise, $command)->shouldBeCalled();

        $event = new Event('some-event', ['command' => $command]);
        (new ConfigureCommandListener($state, $repo, $runnerManager->reveal()))->__invoke($event);
    }

    /**
     * @return array
     */
    public function configurableCommands()
    {
        return [
            ['verify'],
            ['run'],
        ];
    }

    /**
     * @dataProvider nonConfigurableCommands
     * @param string $commandName
     */
    public function testInputIsNotConfiguredForCorrectCommands($commandName)
    {
        $command = new CommandDefinition($commandName, [], function () {
        });

        $state    = new UserState([], 'Exercise 1');
        $exercise = new CliExerciseImpl('Exercise 1');
        $repo     = new ExerciseRepository([$exercise]);

        $runnerManager = $this->prophesize(RunnerManager::class);

        $event = new Event('some-event', ['command' => $command]);
        (new ConfigureCommandListener($state, $repo, $runnerManager->reveal()))->__invoke($event);

        $runnerManager->configureInput($exercise, $command)->shouldNotHaveBeenCalled();
    }

    /**
     * @return array
     */
    public function nonConfigurableCommands()
    {
        return [
            ['print'],
            ['help'],
            ['credits'],
            ['menu'],
        ];
    }
}
