<?php

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use Countable;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * Exercise repository, use to locate individual/all exercises by certain criteria.
 *
 * @implements IteratorAggregate<int, ExerciseInterface>
 */
class ExerciseRepository implements IteratorAggregate, Countable
{
    /**
     * @var array<ExerciseInterface>
     */
    private $exercises;

    /**
     * Requires an array of `ExerciseInterface` instances.
     *
     * @param array<ExerciseInterface> $exercises
     */
    public function __construct(array $exercises)
    {
        $this->exercises = array_map(function (ExerciseInterface $exercise) {
            return $this->validateExercise($exercise);
        }, $exercises);
    }

    /**
     * @param ExerciseInterface $exercise
     * @return ExerciseInterface
     */
    private function validateExercise(ExerciseInterface $exercise): ExerciseInterface
    {
        $type = $exercise->getType();

        $requiredInterface = $type->getExerciseInterface();

        if (!$exercise instanceof $requiredInterface) {
            throw InvalidArgumentException::missingImplements($exercise, $requiredInterface);
        }

        return $exercise;
    }

    /**
     * Retrieve all of the exercises as an array.
     *
     * @return array<ExerciseInterface>
     */
    public function findAll(): array
    {
        return $this->exercises;
    }

    /**
     * Find an exercise by it's name. If it does not exist
     * an `InvalidArgumentException` exception is thrown.
     *
     * @param string $name
     * @return ExerciseInterface
     * @throws InvalidArgumentException
     */
    public function findByName(string $name): ExerciseInterface
    {
        foreach ($this->exercises as $exercise) {
            if ($name === $exercise->getName()) {
                return $exercise;
            }
        }

        throw new InvalidArgumentException(sprintf('Exercise with name: "%s" does not exist', $name));
    }

    /**
     * Get the names of each exercise as an array.
     *
     * @return array<string>
     */
    public function getAllNames(): array
    {
        return array_map(function (ExerciseInterface $exercise) {
            return $exercise->getName();
        }, $this->exercises);
    }

    /**
     * Get the number of exercises contained within the repository.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->exercises);
    }

    /**
     * Allow to iterate over the repository with `foreach`.
     *
     * @return ArrayIterator<int, ExerciseInterface>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->exercises);
    }
}
