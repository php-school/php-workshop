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
