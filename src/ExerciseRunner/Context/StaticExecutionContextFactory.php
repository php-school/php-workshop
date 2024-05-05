<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;
use Symfony\Component\Filesystem\Filesystem;

class StaticExecutionContextFactory extends ExecutionContextFactory
{
    public function __construct(private TestContext $context)
    {
    }

    public function fromInputAndExercise(Input $input, ExerciseInterface $exercise): ExecutionContext
    {
        return $this->context;
    }
}
