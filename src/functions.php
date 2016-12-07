<?php

if (!function_exists('mb_str_pad')) {

    /**
     * @param string $input
     * @param int $padLength
     * @param string $padString
     * @param int $padType
     * @return string
     */
    function mb_str_pad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT)
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
    function camel_case_to_kebab_case($string)
    {
        return preg_replace_callback('/[A-Z]/', function ($matches) {
            return '-' . strtolower($matches[0]);
        }, $string);
    }
}
