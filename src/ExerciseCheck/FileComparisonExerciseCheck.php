<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\ExerciseCheck;

/**
 * This interface should be implemented when you require the check `\PhpSchool\PhpWorkshop\Check\FileComparisonCheck`
 * in your exercise.
 */
interface FileComparisonExerciseCheck
{
    /**
     * @return array<string>
     */
    public function getFilesToCompare(): array;
}
