<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use function PhpSchool\PhpWorkshop\canonicalise_path;

class ProblemFileDoesNotExistException extends \RuntimeException
{
    public static function fromFile(string $file): self
    {
        return new self(sprintf(
            'Exercise problem file: "%s" does not exist or is not readable',
            canonicalise_path($file),
        ));
    }
}
