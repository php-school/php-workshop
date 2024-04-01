<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

interface RunnerContext
{
    public function getExecutionContext(): ExecutionContext;
}
