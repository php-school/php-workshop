<?php

declare(strict_types=1);

use PhpSchool\PhpWorkshop\Utils\Collection;
use PhpSchool\PhpWorkshop\Utils\StringUtils;

if (!function_exists('mb_str_pad')) {

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
}

if (!function_exists('camel_case_to_kebab_case')) {

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
}

if (!function_exists('canonicalise_path')) {

    /**
     * @param string $path
     * @return string
     */
    function canonicalise_path(string $path): string
    {
        return StringUtils::canonicalisePath($path);
    }
}

if (!function_exists('pluralise')) {

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
}

if (!function_exists('collect')) {

    /**
     * @template T
     * @param array<T> $array
     * @return Collection<T>
     */
    function collect(array $array): Collection
    {
        return new Collection($array);
    }
}


if (!function_exists('any')) {

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
}
