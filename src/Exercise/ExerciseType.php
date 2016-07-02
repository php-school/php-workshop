<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use MyCLabs\Enum\Enum;
use PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner;
use PhpSchool\PhpWorkshop\ExerciseRunner\CliRunner;

/**
 * This class is a ENUM which represents the types that exercises can be. Instantiation looks like:
 *
 * ```php
 * $typeCli = ExerciseType::CLI();
 * $typeCgi = ExerciseType::CGI();
 * ```
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseType extends Enum
{
    const CLI = CliRunner::class;
    const CGI = CgiRunner::class;
}
