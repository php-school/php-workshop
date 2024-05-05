<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;

class ExecutionContextFactory
{
    public function fromInputAndExercise(Input $input, ExerciseInterface $exercise): ExecutionContext
    {
        return new ExecutionContext(
            dirname($input->getRequiredArgument('program')),
            System::randomTempDir(),
            $exercise,
            $input
        );
    }
}
