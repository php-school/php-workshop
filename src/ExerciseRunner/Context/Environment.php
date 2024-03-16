<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;

class Environment
{
    public function __construct(
        public ExecutionContext $context,
        public string $workingDirectory,
    ) {}

}