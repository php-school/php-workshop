<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Trait ResultTrait
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
     * @return string
     */
    public function getCheckName()
    {
        return $this->check->getName();
    }
}
