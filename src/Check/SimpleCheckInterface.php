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
     * Run this check before exercise verifying
     *
     * @return string
     */
    const CHECK_BEFORE = 'before';

    /**
     * Run this check after exercise verifying
     *
     * @return string
     */
    const CHECK_AFTER = 'after';

    /**
     * Can this check run this exercise?
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType);

    /**
     * The check is ran against an exercise and a filename which
     * will point to the student's solution.
     *
     * If the check is successful then an instance of
     * `PhpSchool\PhpWorkshop\Result\SuccessInterface` should be returned. If the check is not
     * successful then an instance of `PhpSchool\PhpWorkshop\Result\FailureInterface`
     * should be returned.
     *
     * @param ExerciseInterface $exercise The exercise to check against.
     * @param string $fileName The absolute path to the student's submission.
     * @return ResultInterface The result of the check.
     */
    public function check(ExerciseInterface $exercise, $fileName);

    /**
     * either static::CHECK_BEFORE | static::CHECK_AFTER
     *
     * @return string
     */
    public function getPosition();
}
