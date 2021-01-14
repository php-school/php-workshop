<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

class AssetsNotInitialisedException extends RuntimeException
{
    public static function new(): self
    {
        return new self('Assets not initialised with a base path');
    }
}
