<?php

declare(strict_types=1);

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
