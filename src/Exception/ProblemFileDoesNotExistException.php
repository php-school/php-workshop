<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

class ProblemFileDoesNotExistException extends \RuntimeException
{
    public static function fromFile(string $file): self
    {
        return new self("Exercise problem file: '$file' does not exist or is not readable");
    }
}
