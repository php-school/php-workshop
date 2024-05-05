<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Environment\CliTestEnvironment;
use PhpSchool\PhpWorkshop\Environment\TestEnvironment;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class CliExerciseImpl implements ExerciseInterface, CliExercise
{
    private string $name;
    private SolutionInterface $solution;
    private string $problem = '';
    private CliTestEnvironment $environment;

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

    public function setProblem(string $problem): void
    {
        $this->problem = $problem;
    }

    public function getProblem(): string
    {
        return $this->problem;
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }

    public function configure(ExerciseDispatcher $dispatcher, RunnerContext $context): void
    {
    }

    public function setTestEnvironment(CliTestEnvironment $environment): void
    {
        $this->environment = $environment;
    }

    public function defineTestEnvironment(): CliTestEnvironment
    {
        return $this->environment;
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }

    public function getRequiredChecks(): array
    {
        return [];
    }
}
