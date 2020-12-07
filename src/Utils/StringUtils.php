<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class StringUtils
{
    public static function canonicalisePath(string $filename): string
    {
        $path = [];
        foreach(explode('/', $filename) as $part) {
            // ignore parts that have no value
            if (strlen($part) === 0 || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                $path[] = $part;
            } else if (count($path) > 0) {
                array_pop($path);
            } else {
                throw new RuntimeException('Climbing above the root is not permitted.');
            }
        }

        return $filename[0] === '/'
            ? '/' . implode('/', $path)
            : implode('/', $path);
    }
}
