<?php

namespace PhpSchool\PhpWorkshop\Utils;

class Path
{
    public static function join(string $base, string ...$parts): string
    {
        return implode(
            '/',
            array_merge(
                [rtrim($base, '/')],
                array_map(function (string $part) {
                    return trim($part, '/');
                }, $parts)
            )
        );
    }
}
