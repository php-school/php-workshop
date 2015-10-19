<?php

namespace PhpSchool\PhpWorkshopTest;

use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\UserState;

/**
 * Class UserStateTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserStateTest extends PHPUnit_Framework_TestCase
{
    public function testWithNoCurrentExercisesOrCompleted()
    {
        $state = new UserState;
        $this->assertFalse($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
        $this->assertNull($state->getCurrentExercise());
    }

    public function testWithCurrentExerciseButNoCompleted()
    {
        $state = new UserState([], 'exercise1');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertEmpty($state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());
    }

    public function testWithCurrentExerciseAndCompleted()
    {
        $state = new UserState(['exercise1'], 'exercise2');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
        $this->assertSame('exercise2', $state->getCurrentExercise());
    }

    public function testWithCompletedExerciseButNoCurrent()
    {
        $state = new UserState(['exercise1']);
        $this->assertFalse($state->isAssignedExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
        $this->assertNull($state->getCurrentExercise());
    }

    public function testAddCompletedExercise()
    {
        $state = new UserState([], 'exercise1');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame([], $state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());

        $state->addCompletedExercise('exercise1');

        $this->assertSame(['exercise1'], $state->getCompletedExercises());
        $this->assertSame('exercise1', $state->getCurrentExercise());
    }

    public function testSetCompletedExercise()
    {
        $state = new UserState(['exercise1']);
        $this->assertFalse($state->isAssignedExercise());
        $this->assertNull($state->getCurrentExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());

        $state->setCurrentExercise('exercise2');
        $this->assertTrue($state->isAssignedExercise());
        $this->assertSame('exercise2', $state->getCurrentExercise());
        $this->assertSame(['exercise1'], $state->getCompletedExercises());
    }

    public function testCompletedExercise()
    {
        $state = new UserState(['exercise1']);
        $this->assertTrue($state->completedExercise('exercise1'));
    }
}
