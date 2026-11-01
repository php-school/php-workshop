<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

/**
 * One solution in the sandbox, eg. the student's. Every run of it happens in its directory and listens on its port.
 */
final class Workspace
{
    /**
     * @param int|null $port picked by the sandbox when the scenario exposes a port, eg. 54321
     */
    public function __construct(public readonly string $directory, public readonly ?int $port = null) {}
}
