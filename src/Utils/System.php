<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

class System
{
    public static function realpath(string $path): string
    {
        $realpath = realpath($path);

        if (false === $realpath) {
            throw new \RuntimeException(sprintf('Failed to get realpath of "%s"', $path));
        }

        return $realpath;
    }

    public static function tempDir(): string
    {
        return self::realpath(sys_get_temp_dir());
    }
}
