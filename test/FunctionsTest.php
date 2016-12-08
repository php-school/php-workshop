<?php

namespace PhpSchool\PhpWorkshopTest;

use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider mbStrPadProvider
     *
     * @param string $string
     * @param string $pad
     * @param string $expected
     */
    public function testMbStrPad($string, $pad, $expected)
    {
        self::assertSame(mb_str_pad($string, $pad), $expected);
    }

    /**
     * @return array
     */
    public function mbStrPadProvider()
    {
        return [
            ['hello', 10, 'hello     '],
            ['hello😂', 10, 'hello😂    '],
        ];
    }

    /**
     * @dataProvider camelCaseToKebabCaseProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testCamelCaseToKebabCase($string, $expected)
    {
        self::assertSame(camel_case_to_kebab_case($string), $expected);
    }

    /**
     * @return array
     */
    public function camelCaseToKebabCaseProvider()
    {
        return [
            ['camelCase', 'camel-case'],
            [
                'educationIsThePassportToTheFutureForTomorrowBelongsToThoseWhoPrepareForItToday',
                'education-is-the-passport-to-the-future-for-tomorrow-belongs-to-those-who-prepare-for-it-today'
            ]
        ];
    }
}
