<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshop\Utils\System;

class Environment implements \Stringable
{
    public function __construct(
        public ExecutionContext $context,
        public string $workingDirectory,
    ) {
    }

    public function __toString(): string
    {
        return $this->workingDirectory;
    }
}
