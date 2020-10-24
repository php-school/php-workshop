<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Patch;

/**
 * This interface, when implemented by an exercise, tells the workshop framework that the exercise
 * would like to patch the student's solution. This might include adding code to the top of the file like
 * injecting variables. The exercise should implement the `getPatch()` method and it should return a `Patch`.
 * See [Patching Exercise Submissions](https://www.phpschool.io/docs/reference/patching-exercise-solutions) for
 * more details.
 */
interface SubmissionPatchable
{
    /**
     * Get the patch.
     *
     * @return Patch
     */
    public function getPatch(): Patch;
}
