<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Util;

use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;

class SystemTest extends TestCase
{
    public function testRealpathThrowsOnFailure()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get realpath of "non_existing_file.txt"');

        System::realpath('non_existing_file.txt');
    }

    public function testRealpathReturnsFullPath()
    {
        self::assertSame(realpath(__DIR__), System::realpath(__DIR__));
    }

    public function testTempDir()
    {
        self::assertSame(realpath(sys_get_temp_dir()), System::tempDir());
    }
}
