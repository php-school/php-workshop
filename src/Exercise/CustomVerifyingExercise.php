<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface CustomVerifyingExercise
{
    /**
     * @return ResultInterface
     */
    public function verify();
}
