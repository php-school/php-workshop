<?php

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * Class ExerciseRepository
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRepository implements IteratorAggregate, Countable
{
    /**
     * @var ExerciseInterface[]
     */
    private $exercises;

    /**
     * @param ExerciseInterface[] $exercises
     */
    public function __construct(array $exercises)
    {
        //type safety
        $this->exercises = array_map(function (ExerciseInterface $e) {
            return $e;
        }, $exercises);
    }

    /**
     * @return ExerciseInterface[]
     */
    public function findAll()
    {
        return $this->exercises;
    }

    /**
     * @param string $name
     * @return ExerciseInterface
     */
    public function findByName($name)
    {
        foreach ($this->exercises as $exercise) {
            if ($name === $exercise->getName()) {
                return $exercise;
            }
        }

        throw new \InvalidArgumentException(sprintf('Exercise with name: "%s" does not exist', $name));
    }

    /**
     * @return array
     */
    public function getAllNames()
    {
        return array_map(function (ExerciseInterface $exercise) {
            return $exercise->getName();
        }, $this->exercises);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->exercises);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->exercises);
    }
}
