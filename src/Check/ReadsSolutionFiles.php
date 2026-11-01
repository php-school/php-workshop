<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;

/**
 * A check that reads files the solutions wrote when they ran, eg. a database. The sandbox makes sure they are in the
 * solution's directory afterwards.
 */
interface ReadsSolutionFiles
{
    /**
     * @return list<string> relative to the solution's directory, eg. ['db.sqlite']
     */
    public function getSolutionFiles(ExerciseInterface $exercise): array;
}
