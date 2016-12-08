<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
