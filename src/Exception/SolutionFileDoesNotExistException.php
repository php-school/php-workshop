<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

/**
 * Represents the situation where an exercise requires compares a user generated file with
 * one provided with the solution but it does not exist
 */
class SolutionFileDoesNotExistException extends RuntimeException
{
    /**
     * Static constructor to create an instance from the expected filename.
     */
    public static function fromExpectedFile(string $expectedFile): self
    {
        return new self(sprintf('File: "%s" does not exist in solution folder', $expectedFile));
    }
}
