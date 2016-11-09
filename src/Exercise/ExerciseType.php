<?php

namespace PhpSchool\PhpWorkshop\Exercise;

use MyCLabs\Enum\Enum;

/**
 * This class is a ENUM which represents the types that exercises can be. Instantiation looks like:
 *
 * ```php
 * $typeCli = ExerciseType::CLI();
 * $typeCgi = ExerciseType::CGI();
 * $typeCustom = ExerciseType::CUSTOM();
 * ```
 *
 * @package PhpSchool\PhpWorkshop\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ExerciseType extends Enum
{
    const CLI    = 'CLI';
    const CGI    = 'CGI';
    const CUSTOM = 'CUSTOM';

    /**
     * Map of exercise types to the required interfaces exercises of that particular
     * type should implement.
     *
     * @var array
     */
    private static $exerciseTypeToExerciseInterfaceMap = [
        self::CLI    => CliExercise::class,
        self::CGI    => CgiExercise::class,
        self::CUSTOM => CustomExercise::class,
    ];

    /**
     * Get the FQCN of the interface this exercise should implement for this
     * exercise type.
     *
     * @return string
     */
    public function getExerciseInterface()
    {
        return static::$exerciseTypeToExerciseInterfaceMap[$this->getKey()];
    }
}
