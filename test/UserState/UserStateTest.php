<?php

namespace PhpSchool\PhpWorkshopTest\UserState;

use PhpSchool\PhpWorkshop\Exception\ExerciseNotAssignedException;
use PhpSchool\PhpWorkshop\UserState\UserState;
use PHPUnit\Framework\TestCase;

class UserStateTest extends TestCase
{
    public function testWithNoCurrentExercisesOrCompleted(): void
    {
        $state = new UserState();
        $this->assertFalse($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
    }

    public function testGetCurrentExerciseThrowsExceptionIfNonAssigned(): void
    {
        $this->expectException(ExerciseNotAssignedException::class);
        $this->expectExceptionMessage('Student has no exercise assigned');

        $state = new UserState();
        $this->assertFalse($state->isAssignedExercise());
        $state->getCurrentExercise();
    }

    public function testWithCurrentExerciseButNoCompleted(): void
    {
        $state = new UserState([], 'exercise1');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());
    }

    public function testWithCurrentExerciseAndCompleted(): void
    {
        $state = new UserState(['exercise1'], 'exercise2');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
        $this->assertSame('exercise2', $state->getCurrentExercise());
    }

    public function testWithCompletedExerciseButNoCurrent(): void
    {
        $state = new UserState(['exercise1']);
        $this->assertFalse($state->isAssignedExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
    }

    public function testAddCompletedExercise(): void
    {
        $state = new UserState([], 'exercise1');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame([], $state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());

        $state->addCompletedExercise('exercise1');

        $this->assertSame(['exercise1'], $state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());
    }

    public function testSetCompletedExercise(): void
    {
        $state = new UserState(['exercise1']);
        $this->assertFalse($state->isAssignedExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());

        $state->setCurrentExercise('exercise2');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame('exercise2', $state->getCurrentExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
    }

    public function testCompletedExercise(): void
    {
        $state = new UserState(['exercise1']);
        $this->assertTrue($state->completedExercise('exercise1'));
    }
}
