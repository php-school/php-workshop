<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;

/**
 * This interface describes all of the methods an exercise
 * should implement.
 */
interface ExerciseInterface
{
    /**
     * Get the name of the exercise, like `Hello World!`.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Return the type of exercise. This is an ENUM. See `PhpSchool\PhpWorkshop\Exercise\ExerciseType`.
     *
     * @return ExerciseType
     */
    public function getType(): ExerciseType;

    /**
     * Get the absolute path to the markdown file which contains the exercise problem.
     *
     * @return string
     */
    public function getProblem(): string;

    /**
     * Subscribe to events triggered throughout the verification process
     */
    public function defineListeners(EventDispatcher $dispatcher): void;

    /**
     * This is where the exercise specifies the extra checks it may require.
     *
     * @return array<class-string>
     */
    public function getRequiredChecks(): array;

    /**
     * A short description of the exercise.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Allows to perform some cleanup after the exercise solution's have been executed, for example
     * remove files, close DB connections.
     *
     * @return void
     */
    public function tearDown(): void;
}
