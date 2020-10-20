<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * A result to wrap multiple results
 */
interface ResultGroupInterface extends ResultInterface
{
    /**
     * @return bool
     */
    public function isSuccessful();

    /**
     * @return ResultInterface[]
     */
    public function getResults();
}
