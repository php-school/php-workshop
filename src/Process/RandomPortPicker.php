<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

/**
 * A random high port, which is unlikely to be in use.
 */
final class RandomPortPicker implements PortPicker
{
    public function pick(): int
    {
        return random_int(20000, 59999);
    }
}
