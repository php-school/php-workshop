<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * The base result interface, on it's own does not mean much, instead the
 * interfaces `PhpSchool\PhpWorkshop\Result\SuccessInterface` & `PhpSchool\PhpWorkshop\Result\FailureInterface`
 * should be used.
 */
interface ResultInterface
{
    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName();
}
