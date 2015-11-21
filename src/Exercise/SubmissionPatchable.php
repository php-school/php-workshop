<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\SubmissionPatch;

/**
 * Interface SubmissionPatchable
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SubmissionPatchable
{
    /**
     * @return SubmissionPatch
     */
    public function getPatch();
}
