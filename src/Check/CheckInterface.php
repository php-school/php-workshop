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
     * Return the check's name
     *
     * @return string
     */
    public function getName();

    /**
     * Which Exercise Type this
     * checks applies to
     * @see \PhpSchool\PhpWorkshop\Exercise\ExerciseType
     *
     * @return ExerciseType
     */
    public function appliesTo();

    /**
     * This returns the interface the exercise should implement
     * when requiring this check
     *
     * @return string
     */
    public function getExerciseInterface();
}
