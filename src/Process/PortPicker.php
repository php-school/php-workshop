<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

/**
 * Picks the port on the docker host that a container's port is published on. Swap it for one that remembers ports in
 * use when verifies run at the same time on a shared docker host.
 */
interface PortPicker
{
    public function pick(): int;
}
