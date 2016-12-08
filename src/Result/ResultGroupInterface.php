<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
