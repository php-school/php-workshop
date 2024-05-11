<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Utils;

use PhpSchool\PhpWorkshop\Utils\StringUtils;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;

class StringUtilsTest extends TestCase
{
    public function testCanonicalisePath(): void
    {
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/././to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path///to/file'));
        $this->assertEquals('/path/to/file', StringUtils::canonicalisePath('/path/to/file'));
        $this->assertEquals('/', StringUtils::canonicalisePath('/path/to/../../'));
        $this->assertEquals('/path', StringUtils::canonicalisePath('/path/to/some/../../'));
        $this->assertEquals('/some', StringUtils::canonicalisePath('/path/../some/'));
        $this->assertEquals('some', StringUtils::canonicalisePath('path/../some/'));
    }

    public function testExceptionIsThrownIfTryingToTraverseUpPastRoom(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Climbing above the root is not permitted.');

        StringUtils::canonicalisePath('/path/to/../../../');
    }

    /**
     * @dataProvider pluraliseMultipleProvider
     */
    public function testPluraliseWithMultipleValues(string $string, string $expected): void
    {
        $props = ['propOne', 'propTwo'];
        $this->assertEquals($expected, StringUtils::pluralise($string, $props, implode('" & "', $props)));
    }

    public function pluraliseMultipleProvider(): array
    {
        return [
            [
                'Property "%s" should not have changed type',
                'Properties "propOne" & "propTwo" should not have changed type',
            ],
            ['Property "%s" was not promoted', 'Properties "propOne" & "propTwo" were not promoted'],
            ['Property "%s" was missing', 'Properties "propOne" & "propTwo" were missing'],
            ['Visibility changed for property "%s"', 'Visibility changed for properties "propOne" & "propTwo"'],
        ];
    }

    /**
     * @dataProvider pluraliseSingularProvider
     */
    public function testPluraliseWithSingularValues(string $string, string $expected): void
    {
        $props = ['propOne'];
        $this->assertEquals($expected, StringUtils::pluralise($string, $props, implode('" & "', $props)));
    }

    public function pluraliseSingularProvider(): array
    {
        return [
            ['Property "%s" should not have changed type', 'Property "propOne" should not have changed type'],
            ['Property "%s" was not promoted', 'Property "propOne" was not promoted'],
            ['Property "%s" was missing', 'Property "propOne" was missing'],
            ['Visibility changed for property "%s"', 'Visibility changed for property "propOne"'],
        ];
    }
}
