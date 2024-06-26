<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Exercise\ProvidesInitialCode;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class ExerciseWithInitialCode implements ExerciseInterface, ProvidesInitialCode
{
    public function getName(): string
    {
        return 'exercise-with-initial-code';
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
    }

    public function getSolution(): string
    {
        // TODO: Implement getSolution() method.
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
        // TODO: Implement getType() method.
    }

    public function getInitialCode(): SolutionInterface
    {
        return SingleFileSolution::fromFile(__DIR__ . '/initial-code/init-solution.php');
    }

    public function getRequiredChecks(): array
    {
        return [];
    }

    public function defineListeners(EventDispatcher $dispatcher): void {}
}
