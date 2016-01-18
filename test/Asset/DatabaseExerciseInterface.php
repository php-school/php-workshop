<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\CliExercise;
use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\DatabaseExerciseCheck;

/**
 * interface DatabaseExercise
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface DatabaseExerciseInterface extends ExerciseInterface, DatabaseExerciseCheck, CliExercise
{
}
