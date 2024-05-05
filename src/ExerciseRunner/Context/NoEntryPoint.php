<?php

namespace PhpSchool\PhpWorkshop\ExerciseRunner\Context;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class NoEntryPoint extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('No entry point provided');
    }
}
