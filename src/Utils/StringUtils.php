<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class StringUtils
{
    /**
     * @var array<string,string>
     */
    private static $pluraliseSearchReplace = [
        'Property "%s" was' => 'Properties "%s" were',
        'Property' => 'Properties',
        'property' => 'properties',
    ];

    public static function canonicalisePath(string $filename): string
    {
        $path = [];
        foreach (explode('/', $filename) as $part) {
            // ignore parts that have no value
            if (strlen($part) === 0 || $part === '.') {
                continue;
            }

            if ($part !== '..') {
                $path[] = $part;
            } elseif (count($path) > 0) {
                array_pop($path);
            } else {
                throw new RuntimeException('Climbing above the root is not permitted.');
            }
        }

        return $filename[0] === '/'
            ? '/' . implode('/', $path)
            : implode('/', $path);
    }


    /**
     * @param string $string
     * @param array<mixed> $items
     * @param string ...$args
     * @return string
     */
    public static function pluralise(string $string, array $items, string ...$args): string
    {
        if (count($items) <= 1) {
            return vsprintf($string, $args);
        }

        return vsprintf(
            str_replace(
                array_keys(self::$pluraliseSearchReplace),
                array_values(self::$pluraliseSearchReplace),
                $string,
            ),
            $args,
        );
    }
}
