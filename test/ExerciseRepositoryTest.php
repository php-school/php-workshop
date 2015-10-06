<?php

namespace PhpWorkshop\PhpWorkshopTest;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;
use PhpWorkshop\PhpWorkshop\ExerciseRepository;

/**
 * Class ExerciseRepositoryTest
 * @package PhpWorkshop\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function testFindAll()
    {
        $exercises = [
            $this->getMock(ExerciseInterface::class),
            $this->getMock(ExerciseInterface::class),
        ];

        $repo = new ExerciseRepository($exercises);

        $this->assertSame($exercises, $repo->findAll());
    }

    public function testFindByName()
    {
        $exercise1 = $this->getMock(ExerciseInterface::class);
        $exercise2 = $this->getMock(ExerciseInterface::class);

        $exercise1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));

        $exercise2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise2'));

        $repo = new ExerciseRepository([$exercise1, $exercise2]);
        $this->assertSame($exercise2, $repo->findByName('exercise2'));
    }

    public function testFindByNameThrowsExceptionIfNotFound()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Exercise with name: "exercise1" does not exist'
        );

        $repo = new ExerciseRepository([]);
        $repo->findByName('exercise1');
    }

    public function testGetAllNames()
    {
        $exercise1 = $this->getMock(ExerciseInterface::class);
        $exercise2 = $this->getMock(ExerciseInterface::class);

        $exercise1
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise1'));

        $exercise2
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('exercise2'));

        $repo = new ExerciseRepository([$exercise1, $exercise2]);
        $this->assertSame(['exercise1', 'exercise2'], $repo->getAllNames());
    }
}
