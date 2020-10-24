<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Solution;

/**
 * Interface for reference solution representations.
 */
interface SolutionInterface
{
    /**
     * Get the entry point. This is the PHP file that php would execute in order to run the
     * program. This should be the absolute path.
     *
     * @return string
     */
    public function getEntryPoint(): string;

    /**
     * Get all the files which are contained with the solution.
     *
     * @return array<SolutionFile>
     */
    public function getFiles(): array;

    /**
     * Get the absolute path to the directory containing the solution.
     *
     * @return string
     */
    public function getBaseDirectory(): string;

    /**
     * Whether or not the solution has a `composer.json` & `composer.lock` file.
     *
     * @return bool
     */
    public function hasComposerFile(): bool;
}
