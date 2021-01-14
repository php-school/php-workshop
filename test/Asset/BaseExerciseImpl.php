<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\BaseExerciseTrait;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;

class BaseExerciseImpl implements ExerciseInterface
{
    use BaseExerciseTrait;

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ExerciseType
    {
        return new ExerciseType(ExerciseType::CLI);
    }

    public function getDescription(): string
    {
        return $this->getName();
    }
}
