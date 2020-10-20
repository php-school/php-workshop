<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;

interface DatabaseExerciseInterface extends ExerciseInterface, DatabaseExerciseCheck, CliExercise
{
}
