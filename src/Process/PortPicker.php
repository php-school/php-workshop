<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

/**
 * Picks a port, eg. the one an exercise's code listens on, or the one DockerSandbox publishes it on.
 */
interface PortPicker
{
    public function pick(): int;
}
