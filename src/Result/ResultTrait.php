<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Helper to proxy the `getCheckName()` method through to the `PhpSchool\PhpWorkshop\Check\CheckInterface`
 * instance.
 *
 * @package PhpSchool\PhpWorkshop\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
trait ResultTrait
{
    /**
     * @var CheckInterface
     */
    private $check;
    
    /**
     * Get the check name from the underlying check. Assumes that the `check` property has been
     * assigned an instance of `PhpSchool\PhpWorkshop\Check\CheckInterface`.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->check->getName();
    }
}
