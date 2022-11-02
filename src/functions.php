<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Utils\Collection;
use PhpSchool\PhpWorkshop\Utils\StringUtils;

/**
 * @param string $input
 * @param int $padLength
 * @param string $padString
 * @param int $padType
 * @return string
 */
function mb_str_pad(string $input, int $padLength, string $padString = ' ', int $padType = STR_PAD_RIGHT): string
{
    $diff = strlen($input) - mb_strlen($input);
    return str_pad($input, $padLength + $diff, $padString, $padType);
}

/**
 * @param string $string
 * @return string
 */
function camel_case_to_kebab_case(string $string): string
{
    return (string) preg_replace_callback('/[A-Z]/', function ($matches) {
        return '-' . strtolower($matches[0]);
    }, $string);
}

/**
 * @param string $path
 * @return string
 */
function canonicalise_path(string $path): string
{
    return StringUtils::canonicalisePath($path);
}

/**
 * @param string $string
 * @param array<mixed> $items
 * @param string[] ...$args
 * @return string
 */
function pluralise(string $string, array $items, string ...$args): string
{
    return StringUtils::pluralise($string, $items, ...$args);
}

/**
 * @template TKey of array-key
 * @template T
 * @param array<TKey, T> $array
 * @return Collection<TKey, T>
 */
function collect(array $array): Collection
{
    /** @var Collection<TKey, T> $collection */
    $collection = new Collection($array);
    return $collection;
}

/**
 * @param array<mixed> $values
 * @param callable $cb
 * @return bool
 */
function any(array $values, callable $cb): bool
{
    return array_reduce($values, function (bool $carry, $value) use ($cb) {
        return $carry || $cb($value);
    }, false);
}
