<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CliScenario;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class CliExerciseImpl implements ExerciseInterface, CliExercise
{
    private string $name;
    private string $problemFile = 'problem-file.md';
    private SolutionInterface $solution;
    private CliScenario $scenario;

    public function __construct(string $name = 'my-exercise')
    {
        $this->name = $name;
        $this->scenario = new CliScenario();
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
        return $this->problemFile;
    }

    public function setProblem(string $problemFile): void
    {
        $this->problemFile = $problemFile;
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public function setScenario(CliScenario $scenario): void
    {
        $this->scenario = $scenario;
    }

    public function defineTestScenario(): CliScenario
    {
        return $this->scenario;
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }

    public function getRequiredChecks(): array
    {
        return [];
    }


    public function defineListeners(EventDispatcher $dispatcher): void {}
}
