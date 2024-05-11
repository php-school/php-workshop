<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

use PhpSchool\PhpWorkshop\Exercise\ExerciseType;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * The interface for simple checks, checks that execute at one defined point, before or after
 * output verification.
 */
interface SimpleCheckInterface extends CheckInterface
{
    /**
     * Run this check before exercise verifying
     *
     * @return string
     */
    public const CHECK_BEFORE = 'before';

    /**
     * Run this check after exercise verifying
     *
     * @return string
     */
    public const CHECK_AFTER = 'after';

    /**
     * Can this check run this exercise?
     *
     * @param ExerciseType $exerciseType
     * @return bool
     */
    public function canRun(ExerciseType $exerciseType): bool;

    /**
     * The check is ran against an exercise and a filename which
     * will point to the student's solution.
     *
     * If the check is successful then an instance of
     * `PhpSchool\PhpWorkshop\Result\SuccessInterface` should be returned. If the check is not
     * successful then an instance of `PhpSchool\PhpWorkshop\Result\FailureInterface`
     * should be returned.
     *
     * @param ExecutionContext $context The current execution context, containing the exercise, input and working directories.
     * @return ResultInterface The result of the check.
     */
    public function check(ExecutionContext $context): ResultInterface;

    /**
     * Either `static::CHECK_BEFORE` | `static::CHECK_AFTER`.
     *
     * @return string
     */
    public function getPosition(): string;
}
