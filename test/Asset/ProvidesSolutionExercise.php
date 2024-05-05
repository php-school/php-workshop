<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesSolution;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\RunnerContext;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class ProvidesSolutionExercise implements ExerciseInterface, ProvidesSolution
{
    public function getName(): string
    {
        return 'exercise-provides-solution';
    }

    public function getType(): ExerciseType
    {
        // TODO: Implement getType() method.
    }

    public function getProblem(): string
    {
        // TODO: Implement getProblem() method.
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    public function tearDown(): void
    {
        // TODO: Implement tearDown() method.
    }

    public function getSolution(): SolutionInterface
    {
        return SingleFileSolution::fromFile(__DIR__ . '/provided-solution/solution.php');
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }

    public function getRequiredChecks(): array
    {
        return [];
    }
}
