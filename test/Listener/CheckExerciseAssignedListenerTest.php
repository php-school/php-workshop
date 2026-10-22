<?php

namespace PhpSchool\PhpWorkshopTest\Listener;

use PHPUnit\Framework\Attributes\DataProvider;
use PhpSchool\PhpWorkshop\CommandDefinition;
use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Listener\CheckExerciseAssignedListener;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckExerciseAssignedListenerTest extends TestCase
{
    #[DataProvider('commandsThatRequireAssignedExercise')]
    public function testExceptionIsThrownIfNoExerciseAssigned(string $commandName): void
    {
        $command = $this->command($commandName);
        $state = new UserState();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No active exercise. Select one from the menu');

        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));
    }

    #[DataProvider('commandsThatRequireAssignedExercise')]
    public function testExceptionIsNotThrownIfExerciseAssigned(string $commandName): void
    {
        $command = $this->command($commandName);
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    public static function commandsThatRequireAssignedExercise(): array
    {
        return [
            ['verify'],
            ['run'],
            ['print'],
        ];
    }

    #[DataProvider('commandsThatDoNotRequireAssignedExercise')]
    public function testExceptionIsNotThrownIfCommandDoesNotRequireAssignedExercise(string $commandName): void
    {
        $command = $this->command($commandName);
        $state = new UserState(['exercise1'], 'exercise1');
        $listener = new CheckExerciseAssignedListener($state);
        $listener->__invoke(new Event('some-event', ['command' => $command]));

        $this->assertTrue($state->isAssignedExercise());
    }

    public static function commandsThatDoNotRequireAssignedExercise(): array
    {
        return [
            ['help'],
            ['credits'],
            ['menu'],
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
