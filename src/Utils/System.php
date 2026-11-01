<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class System
{
    public static function realpath(string $path): string
    {
        $realpath = realpath($path);

        if (false === $realpath) {
            throw new RuntimeException(sprintf('Failed to get realpath of "%s"', $path));
        }

        return $realpath;
    }

    public static function randomTempPath(string $ext): string
    {
        return Path::join(self::realpath(sys_get_temp_dir()), 'php-school', bin2hex(random_bytes(4)) . '.' . $ext);
    }

    public static function tempDir(string $path = ''): string
    {
        return Path::join(self::realpath(sys_get_temp_dir()), 'php-school', $path);
    }

    /**
     * A port nothing listens on, on this machine. Inside a sandbox container every port is free, so it works there too.
     */
    public static function freePort(): int
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

    public static function randomTempDir(): string
    {
        return Path::join(self::realpath(sys_get_temp_dir()), 'php-school', bin2hex(random_bytes(4)));
    }
}
