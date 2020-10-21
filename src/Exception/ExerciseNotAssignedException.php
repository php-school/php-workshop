<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

/**
 * When a student has no exercise assigned
 */
class ExerciseNotAssignedException extends \RuntimeException
{
    /**
     * @return self
     */
    public static function notAssigned(): self
    {
        return new self('Student has no exercise assigned');
    }
}
