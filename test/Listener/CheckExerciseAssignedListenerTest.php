<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Listener\CheckExerciseAssignedListener;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\UserState;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CheckExerciseAssignedListenerTest extends TestCase
{
    /**
     * @dataProvider commandsThatRequireAssignedExercise
     * @param CommandDefinition $command
     */
    public function testExceptionIsThrownIfNoExerciseAssigned(CommandDefinition $command)
    {
        $state = new UserState;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No active exercise. Select one from the menu');

        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));
    }

    /**
     * @dataProvider commandsThatRequireAssignedExercise
     * @param CommandDefinition $command
     */
    public function testExceptionIsNotThrownIfExerciseAssigned(CommandDefinition $command)
    {
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    /**
     * @return array
     */
    public function commandsThatRequireAssignedExercise()
    {
        return [
            [$this->command('verify')],
            [$this->command('run')],
            [$this->command('print')],
        ];
    }

    /**
     * @dataProvider commandsThatDoNotRequireAssignedExercise
     * @param CommandDefinition $command
     */
    public function testExceptionIsNotThrownIfCommandDoesNotRequireAssignedExercise(CommandDefinition $command)
    {
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    /**
     * @return array
     */
    public function commandsThatDoNotRequireAssignedExercise()
    {
        return [
            [$this->command('help')],
            [$this->command('credits')],
            [$this->command('menu')],
        ];
    }

    /**
     * @param $commandName
     * @return \PHPUnit_Framework_MockObject_MockObject|CommandDefinition
     */
    private function command($commandName)
    {
        $command = $this->createMock(CommandDefinition::class);
        $command
            ->expects($this->any())
            ->method('getName')
            ->willReturn($commandName);

        return $command;
    }
}
