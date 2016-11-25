<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface CustomExercise
{
    /**
     * @return ResultInterface
     */
    public function verify();
}
