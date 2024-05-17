<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\CgiExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use Psr\Http\Message\RequestInterface;

class CgiExerciseImpl implements ExerciseInterface, CgiExercise
{
    private string $name;
    private SolutionInterface $solution;
    private CgiScenario $scenario;

    public function __construct(string $name = 'my-exercise')
    {
        $this->name = $name;
        $this->scenario = new CgiScenario();
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

    public function getType(): ExerciseType
    {
        return ExerciseType::CGI();
    }

    public function getRequiredChecks(): array
    {
        return [];
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }

    public function setScenario(CgiScenario $scenario): void
    {
        $this->scenario = $scenario;
    }

    public function defineTestScenario(): CgiScenario
    {
        return $this->scenario;
    }
}
