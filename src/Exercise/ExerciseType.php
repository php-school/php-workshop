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
 * @extends Enum<string>
 */
class ExerciseType extends Enum
{
    public const CLI    = 'CLI';
    public const CGI    = 'CGI';
    public const CUSTOM = 'CUSTOM';

    /**
     * Map of exercise types to the required interfaces exercises of that particular
     * type should implement.
     *
     * @var array<string, class-string>
     */
    private static $exerciseTypeToExerciseInterfaceMap = [
        self::CLI => CliExercise::class,
        self::CGI => CgiExercise::class,
        self::CUSTOM => CustomVerifyingExercise::class,
    ];

    /**
     * Get the FQCN of the interface this exercise should implement for this
     * exercise type.
     *
     * @return class-string
     */
    public function getExerciseInterface(): string
    {
        return static::$exerciseTypeToExerciseInterfaceMap[$this->getKey()];
    }
}
