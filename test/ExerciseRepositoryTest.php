<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseMissingInterface;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;

/**
 * Class ExerciseRepositoryTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testFindAll()
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);

        $this->assertSame($exercises, $repo->findAll());
    }

    public function testFindByName()
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertSame($exercises[1], $repo->findByName('Exercise 2'));
    }

    public function testFindByNameThrowsExceptionIfNotFound()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise with name: "exercise1" does not exist');

        $repo = new ExerciseRepository([]);
        $repo->findByName('exercise1');
    }

    public function testGetAllNames()
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertSame(['Exercise 1', 'Exercise 2'], $repo->getAllNames());
    }

    public function testCount()
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertCount(2, $repo);
    }

    public function testIterator()
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertEquals($exercises, iterator_to_array($repo));
    }

    public function testExceptionIsThrownWhenTryingToAddExerciseWhichDoesNotImplementCorrectInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $message  = '"PhpSchool\PhpWorkshopTest\Asset\CliExerciseMissingInterface" is required to implement ';
        $message .= '"PhpSchool\PhpWorkshop\Exercise\CliExercise", but it does not';
        $this->expectExceptionMessage($message);

        $exercise = new CliExerciseMissingInterface;
        new ExerciseRepository([$exercise]);
    }
}
