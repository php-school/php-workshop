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

    public static function tempDir(string $path = ''): string
    {
        return Path::join(self::realpath(sys_get_temp_dir()), 'php-school', $path);
    }

    public static function randomTempDir(string $path = ''): string
    {
        return Path::join(self::realpath(sys_get_temp_dir()), 'php-school', bin2hex(random_bytes(4)), $path);
    }
}
