<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\ExerciseDispatcher;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;

/**
 * This trait implements many of the methods described in `PhpSchool\PhpWorkshop\Exercise\ExerciseInterface`.
 * It serves as a good base for an exercise, providing useful defaults for many of the methods.
 */
trait BaseExerciseTrait
{
    /**
     * This returns a single file solution named `solution.php` which
     * should exist in `workshop-root/exercises/<exercise-name>/solution/`.
     *
     * This method can be overwritten if the solution consists of multiple files,
     * see [Directory Solution](https://www.phpschool.io/docs/reference/exercise-solutions#directory-solution) for
     * more details.
     */
    public function getSolution(): SolutionInterface
    {
        return SingleFileSolution::fromFile(ExerciseAssets::getAssetPath($this, 'solution', 'solution.php'));
    }

    /**
     * This returns the problem file path, which is assumed to exist in
     * `workshop-root/exercises/<exercise-name>/problem/` as a file named `problem.md`.
     */
    public function getProblem(): string
    {
        return ExerciseAssets::getAssetPath($this, 'problem', 'problem.md');
    }

    /**
     * Get the absolute path of a given valid asset type and filename
     * `workshop-root/exercises/<exercise-name>/<type>/<fileName>`
     */
    public function getAssetPath(string $type, string $fileName): string
    {
        return ExerciseAssets::getAssetPath($this, $type, $fileName);
    }

    /**
     * Allows to perform some cleanup after the exercise solution's have been executed, for example
     * remove files, close DB connections.
     *
     * @return void
     */
    public function tearDown(): void
    {
    }

    /**
     * This method is implemented as empty by default, if you want to add additional checks or listen
     * to events, you should override this method.
     *
     * @param ExerciseDispatcher $dispatcher
     */
    public function configure(ExerciseDispatcher $dispatcher): void
    {
    }
}
