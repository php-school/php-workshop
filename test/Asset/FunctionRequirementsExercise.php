<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Check\FileComparisonCheck;
use PhpSchool\PhpWorkshop\Check\FunctionRequirementsCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FunctionRequirementsExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;

class FunctionRequirementsExercise implements ExerciseInterface, FunctionRequirementsExerciseCheck
{
    public function getName(): string
    {
        // TODO: Implement getName() method.
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

    public function getArgs(): array
    {
        return []; // TODO: Implement getArgs() method.
    }

    public function getType(): ExerciseType
    {
        return ExerciseType::CLI();
    }

    /**
     * @return string[]
     */
    public function getRequiredFunctions(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getBannedFunctions(): array
    {
        return ['file'];
    }

    public function getRequiredChecks(): array
    {
        return [FunctionRequirementsCheck::class];
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }
}
