<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Check;

/**
 * This check verifies that the student's solution contains usages of some required functions
 * and also does not use certain functions (specified by the exercise).
 */
class FunctionRequirementsBeforeCheck extends FunctionRequirementsCheck
{
    /**
     * This is performed BEFORE executing the student's solution to short circuit further
     * checks from running.
     */
    public function getPosition(): string
    {
        return SimpleCheckInterface::CHECK_BEFORE;
    }
}
