<?php

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class CheckInterface
 * @package PhpSchool\PhpWorkshop\Comparator
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SimpleCheckInterface extends CheckInterface
{
    /**
     * Can this check run this exercise?
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType);

    /**
     * @param ExerciseInterface $exercise
     * @param string $fileName
     * @return ResultInterface
     */
    public function check(ExerciseInterface $exercise, $fileName);
}
