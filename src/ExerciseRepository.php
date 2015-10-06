<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * Class ExerciseRepository
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseRepository
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
}
