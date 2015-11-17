<?php

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class SelfCheck
 * @package PhpSchool\PhpWorkshop\ExerciseCheck
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SelfCheck
{
    /**
     * @param string $fileName
     * @return ResultInterface
     */
    public function check($fileName);
}
