<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Port;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

/**
 * A port nothing listens on, on this machine. Inside a sandbox container every port is free, so exercises use it for
 * the port their code listens on.
 */
final class FreePortPicker implements PortPicker
{
    public function pick(): int
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

    public function release(int $port): void {}
}
