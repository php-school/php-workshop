<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class CheckInterface
 * @package PhpSchool\PhpWorkshop\Comparator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */

interface CheckInterface
{
    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName);

    /**
     * @return bool
     */
    public function breakChainOnFailure();
}
