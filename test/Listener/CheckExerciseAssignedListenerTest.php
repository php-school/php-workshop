<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Listener\CheckExerciseAssignedListener;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckExerciseAssignedListenerTest extends TestCase
{
    /**
     * @dataProvider commandsThatRequireAssignedExercise
     */
    public function testExceptionIsThrownIfNoExerciseAssigned(CommandDefinition $command): void
    {
        $state = new UserState();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No active exercise. Select one from the menu');

        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));
    }

    /**
     * @dataProvider commandsThatRequireAssignedExercise
     */
    public function testExceptionIsNotThrownIfExerciseAssigned(CommandDefinition $command): void
    {
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    public function commandsThatRequireAssignedExercise(): array
    {
        return [
            [$this->command('verify')],
            [$this->command('run')],
            [$this->command('print')],
        ];
    }

    /**
     * @dataProvider commandsThatDoNotRequireAssignedExercise
     */
    public function testExceptionIsNotThrownIfCommandDoesNotRequireAssignedExercise(CommandDefinition $command): void
    {
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    public function commandsThatDoNotRequireAssignedExercise(): array
    {
        return [
            [$this->command('help')],
            [$this->command('credits')],
            [$this->command('menu')],
        ];
    }

    /**
     * @return MockObject|CommandDefinition
     */
    private function command(string $commandName): MockObject
    {
        $command = $this->createMock(CommandDefinition::class);
        $command
            ->method('getName')
            ->willReturn($commandName);

        return $command;
    }
}
