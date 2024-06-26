<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Event\EventDispatcher;
use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\Solution\SolutionInterface;
use ReflectionClass;

/**
 * This abstract class implements many of the methods described in `PhpSchool\PhpWorkshop\Exercise\ExerciseInterface`.
 * It serves as a good base for an exercise, providing useful defaults for many of the methods.
 */
abstract class AbstractExercise
{
    /**
     * Get the name of the exercise, like `Hello World!`.
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * This returns a single file solution named `solution.php` which
     * should exist in `workshop-root/exercises/<exercise-name>/solution/`.
     *
     * This method can be overwritten if the solution consists of multiple files,
     * see [Directory Solution](https://www.phpschool.io/docs/reference/exercise-solutions#directory-solution) for
     * more details.
     *
     * @return SolutionInterface
     */
    public function getSolution(): SolutionInterface
    {
        return SingleFileSolution::fromFile(
            (string) realpath(
                sprintf(
                    '%s/../../exercises/%s/solution/solution.php',
                    dirname((string) (new ReflectionClass(static::class))->getFileName()),
                    self::normaliseName($this->getName()),
                ),
            ),
        );
    }

    /**
     * This returns the problem file path, which is assumed to exist in
     * `workshop-root/exercises/<exercise-name>/problem/` as a file named `problem.md`.
     *
     * @return string
     */
    public function getProblem(): string
    {
        $name = self::normaliseName($this->getName());
        $dir  = dirname((string) (new ReflectionClass(static::class))->getFileName());
        return sprintf('%s/../../exercises/%s/problem/problem.md', $dir, $name);
    }

    /**
     * Allows to perform some cleanup after the exercise solution's have been executed, for example
     * remove files, close DB connections.
     *
     * @return void
     */
    public function tearDown(): void {}

    /**
     * @param string $name
     * @return string
     */
    public static function normaliseName(string $name): string
    {
        return (string) preg_replace('/[^A-Za-z\-]+/', '', str_replace(' ', '-', strtolower($name)));
    }

    /**
     * @return list<class-string>
     */
    public function getRequiredChecks(): array
    {
        return [];
    }

    public function defineListeners(EventDispatcher $dispatcher): void {}
}
