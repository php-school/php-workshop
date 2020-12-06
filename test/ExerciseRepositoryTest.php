<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseMissingInterface;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseRepository;

class ExerciseRepositoryTest extends TestCase
{
    public function testFindAll(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);

        $this->assertSame($exercises, $repo->findAll());
    }

    public function testFindByName(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertSame($exercises[1], $repo->findByName('Exercise 2'));
    }

    public function testFindByNameThrowsExceptionIfNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Exercise with name: "exercise1" does not exist');

        $repo = new ExerciseRepository([]);
        $repo->findByName('exercise1');
    }

    public function testFindByClassName(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertSame($exercises[0], $repo->findByClassName(CliExerciseImpl::class));
    }

    public function testFindByClassNameThrowsExceptionIfNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Exercise with name: "%s" does not exist', CliExerciseImpl::class));

        $repo = new ExerciseRepository([]);
        $repo->findByClassName(CliExerciseImpl::class);
    }

    public function testGetAllNames(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertSame(['Exercise 1', 'Exercise 2'], $repo->getAllNames());
    }

    public function testCount(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertCount(2, $repo);
    }

    public function testIterator(): void
    {
        $exercises = [
            new CliExerciseImpl('Exercise 1'),
            new CliExerciseImpl('Exercise 2'),
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertEquals($exercises, iterator_to_array($repo));
    }

    public function testExceptionIsThrownWhenTryingToAddExerciseWhichDoesNotImplementCorrectInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $message  = '"PhpSchool\PhpWorkshopTest\Asset\CliExerciseMissingInterface" is required to implement ';
        $message .= '"PhpSchool\PhpWorkshop\Exercise\CliExercise", but it does not';
        $this->expectExceptionMessage($message);

        $exercise = new CliExerciseMissingInterface();
        new ExerciseRepository([$exercise]);
    }
}
