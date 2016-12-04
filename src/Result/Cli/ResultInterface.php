<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface ResultInterface extends \PhpSchool\PhpWorkshop\Result\ResultInterface
{
    /**
     * @return ArrayObject
     */
    public function getArgs();
}
