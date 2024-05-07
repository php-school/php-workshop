<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Check\FileComparisonCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class CliExerciseImpl implements ExerciseInterface, CliExercise
{
    private string $name;
    private SolutionInterface $solution;
    private array $args = [[]];

    public function __construct(string $name = 'my-exercise')
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->name;
    }

    public function setSolution(SolutionInterface $solution): void
    {
        $this->solution = $solution;
    }

    public function getSolution(): SolutionInterface
    {
        return $this->solution;
    }

    public function getProblem(): string
    {
        // TODO: Implement getProblem() method.
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }

    public function getRequiredChecks(): array
    {
        return [];
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }
}
