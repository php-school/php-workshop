<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * Base CLI result interface
 */
interface ResultInterface extends \PhpSchool\PhpWorkshop\Result\ResultInterface
{
    /**
     * Get the arguments associated with this result.
     *
     * @return ArrayObject
     */
    public function getArgs();
}
