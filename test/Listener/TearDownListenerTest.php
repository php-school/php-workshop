<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Listener;

use PhpSchool\PhpWorkshop\Listener\TearDownListener;
use PhpSchool\PhpWorkshop\Utils\System;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class TearDownListenerTest extends TestCase
{
    public function testCleansUpTempDir(): void
    {
        $tempDir = System::tempDir();

        mkdir($tempDir . '/some/path', 0777, true);
        touch($tempDir . '/some.file');
        touch($tempDir . '/some/path/another.file');

        self::assertFileExists($tempDir . '/some.file');
        self::assertFileExists($tempDir . '/some/path/another.file');

        (new TearDownListener(new Filesystem()))->cleanupTempDir();

        self::assertFileDoesNotExist($tempDir . '/some.file');
        self::assertFileDoesNotExist($tempDir . '/some/path/another.file');
    }
}
