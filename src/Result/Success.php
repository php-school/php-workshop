<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class Fail
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success implements SuccessInterface
{
    use ResultTrait;

    /**
     * @param CheckInterface $check
     */
    public function __construct(CheckInterface $check)
    {
        $this->check = $check;
    }
}
