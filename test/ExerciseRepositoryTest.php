<?php

namespace PhpSchool\PhpWorkshopTest;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseInterface;
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
        $exercise1 = $this->prophesize(CliExerciseInterface::class);
        $exercise2 = $this->prophesize(CliExerciseInterface::class);
        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());

        $exercises = [
            $exercise1->reveal(),
            $exercise2->reveal()
        ];

        $repo = new ExerciseRepository($exercises);

        $this->assertSame($exercises, $repo->findAll());
    }

    public function testFindByName()
    {
        $exercise1 = $this->prophesize(CliExerciseInterface::class);
        $exercise2 = $this->prophesize(CliExerciseInterface::class);
        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());
        $exercise1->getName()->willReturn('exercise1');
        $exercise2->getName()->willReturn('exercise2');

        $exercise1 = $exercise1->reveal();
        $exercise2 = $exercise2->reveal();

        $repo = new ExerciseRepository([$exercise1, $exercise2]);
        $this->assertSame($exercise2, $repo->findByName('exercise2'));
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
        $exercise1 = $this->prophesize(CliExerciseInterface::class);
        $exercise2 = $this->prophesize(CliExerciseInterface::class);
        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());
        $exercise1->getName()->willReturn('exercise1');
        $exercise2->getName()->willReturn('exercise2');

        $repo = new ExerciseRepository([$exercise1->reveal(), $exercise2->reveal()]);
        $this->assertSame(['exercise1', 'exercise2'], $repo->getAllNames());
    }

    public function testCount()
    {
        $exercise1 = $this->prophesize(CliExerciseInterface::class);
        $exercise2 = $this->prophesize(CliExerciseInterface::class);
        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());

        $exercises = [
            $exercise1->reveal(),
            $exercise2->reveal()
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertCount(2, $repo);
    }

    public function testIterator()
    {
        $exercise1 = $this->prophesize(CliExerciseInterface::class);
        $exercise2 = $this->prophesize(CliExerciseInterface::class);
        $exercise1->getType()->willReturn(ExerciseType::CLI());
        $exercise2->getType()->willReturn(ExerciseType::CLI());

        $exercises = [
            $exercise1->reveal(),
            $exercise2->reveal()
        ];

        $repo = new ExerciseRepository($exercises);
        $this->assertEquals($exercises, iterator_to_array($repo));
    }
}
