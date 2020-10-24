<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * This interface describes the methods for a CUSTOM type exercise.
 */
interface CustomVerifyingExercise
{
    /**
     * @return ResultInterface
     */
    public function verify(): ResultInterface;
}
