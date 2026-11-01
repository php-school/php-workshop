<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Port;

/**
 * Picks a port, eg. the one an exercise's code listens on, or the one DockerSandbox publishes it on.
 */
interface PortPicker
{
    public function pick(): int;

    /**
     * Done with a picked port, eg. its container is removed.
     */
    public function release(int $port): void;
}
