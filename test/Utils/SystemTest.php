<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Utils;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class SystemTest extends TestCase
{
    public function testRealpathThrowsOnFailure(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to get realpath of "non_existing_file.txt"');

        System::realpath('non_existing_file.txt');
    }

    public function testRealpathReturnsFullPath(): void
    {
        self::assertSame(realpath(__DIR__), System::realpath(__DIR__));
    }

    public function testTempDir(): void
    {
        self::assertSame(realpath(sys_get_temp_dir()) . '/php-school', System::tempDir());
    }

    public function testTempDirWithPath(): void
    {
        $expect = sprintf('%s/php-school/%s', realpath(sys_get_temp_dir()), 'test');
        self::assertSame($expect, System::tempDir('test'));
    }

    public function testRandomTempDir(): void
    {
        self::assertTrue(str_starts_with(System::randomTempDir(), realpath(sys_get_temp_dir()) . '/php-school'));
    }
}
