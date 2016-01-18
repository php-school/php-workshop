<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\ExerciseCheck\SelfCheck;

/**
 * Class SelfCheckExerciseInterface
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface SelfCheckExerciseInterface extends ExerciseInterface, SelfCheck
{
}
