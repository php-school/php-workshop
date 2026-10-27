<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

/**
 * The machine the sandbox runs code on, for exercises that connect to the student's code, eg. a server.
 */
final class SandboxHost
{
    public function __construct(private string $address = '127.0.0.1') {}

    /**
     * The address to connect to published ports on.
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * A port that is free to publish. Asks this machine, so it needs another way when the host is remote.
     */
    public function getFreePort(): int
    {
        $server = @stream_socket_server('tcp://127.0.0.1:0');

        if ($server === false) {
            throw new RuntimeException('Cannot create socket');
        }

        //eg. 127.0.0.1:54321
        $name = (string) stream_socket_get_name($server, false);
        fclose($server);

        return (int) substr($name, (int) strrpos($name, ':') + 1);
    }
}
