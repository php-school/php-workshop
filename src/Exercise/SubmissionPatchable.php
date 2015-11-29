<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Patch;

/**
 * Interface SubmissionPatchable
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SubmissionPatchable
{
    /**
     * @return Patch
     */
    public function getPatch();
}
