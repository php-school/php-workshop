<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;

interface SelfCheckExerciseInterface extends ExerciseInterface, SelfCheck
{
}
