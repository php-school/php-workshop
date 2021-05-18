<?php

namespace PhpSchool\PhpWorkshopTest;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    /**
     * @dataProvider mbStrPadProvider
     */
    public function testMbStrPad(string $string, int $pad, string $expected): void
    {
        self::assertSame(mb_str_pad($string, $pad), $expected);
    }

    public function mbStrPadProvider(): array
    {
        return [
            ['hello', 10, 'hello     '],
            ['hello😂', 10, 'hello😂    '],
        ];
    }

    /**
     * @dataProvider camelCaseToKebabCaseProvider
     */
    public function testCamelCaseToKebabCase(string $string, string $expected): void
    {
        self::assertSame(camel_case_to_kebab_case($string), $expected);
    }

    public function camelCaseToKebabCaseProvider(): array
    {
        return [
            ['camelCase', 'camel-case'],
            [
                'educationIsThePassportToTheFutureForTomorrowBelongsToThoseWhoPrepareForItToday',
                'education-is-the-passport-to-the-future-for-tomorrow-belongs-to-those-who-prepare-for-it-today'
            ]
        ];
    }

    public function testAny(): void
    {
        self::assertEquals(true, any([1, 2, 3, 10, 11], function (int $num) {
            return $num > 10;
        }));

        self::assertEquals(false, any([1, 2, 3, 10, 11], function (int $num) {
            return $num > 11;
        }));
    }
}
