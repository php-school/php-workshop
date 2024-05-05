<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Check\ComposerCheck;
use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseCheck\FileComparisonExerciseCheck;
use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

class FileComparisonExercise implements ExerciseInterface, FileComparisonExerciseCheck
{
    /**
     * @var array<string>
     */
    private array $files;

    private SolutionInterface $solution;

    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.
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
        return ExerciseType::CLI();
    }

    public function getFilesToCompare(): array
    {
        return $this->files;
    }

    public function defineListeners(EventDispatcher $dispatcher): void
    {
    }

    public function getRequiredChecks(): array
    {
        return [ComposerCheck::class];
    }
}
