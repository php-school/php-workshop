<?php

declare(strict_types=1);

namespace Solution;

use PhpSchool\PhpWorkshop\Solution\InTempSolutionMapper;
use PhpSchool\PhpWorkshop\Utils\Path;
use PHPUnit\Framework\TestCase;

class InTempSolutionMapperTest extends TestCase
{
    public function testFileMapping(): void
    {
        $filePath  = Path::join(realpath(sys_get_temp_dir()), 'test.file');
        touch($filePath);

        $mappedFile = InTempSolutionMapper::mapFile($filePath);

        self::assertFileExists($mappedFile);
        self::assertNotSame($filePath, $mappedFile);
        self::assertStringContainsString(realpath(sys_get_temp_dir()), $mappedFile);
    }

    public function testDirectoryMapping(): void
    {
        $tempDir = Path::join(realpath(sys_get_temp_dir()), bin2hex(random_bytes(10)));
        $file  = Path::join($tempDir, 'test.file');
        $inner = Path::join($tempDir, 'innerDir');
        $innerFile  = Path::join($inner, 'test.file');
        @mkdir($tempDir);
        touch($file);
        @mkdir($inner);
        touch($innerFile);

        $mappedDir = InTempSolutionMapper::mapDirectory($tempDir);

        self::assertDirectoryExists($mappedDir);
        self::assertDirectoryExists(Path::join($mappedDir, 'innerDir'));
        self::assertFileExists(Path::join($mappedDir, 'test.file'));
        self::assertFileExists(Path::join($mappedDir, 'innerDir', 'test.file'));
        self::assertNotSame($tempDir, $mappedDir);
        self::assertStringContainsString(realpath(sys_get_temp_dir()), $mappedDir);
    }

    public function testMappingIsInFreshTempDir(): void
    {
        $filePath  = Path::join(realpath(sys_get_temp_dir()), 'test.file');
        touch($filePath);

        $tempDir = Path::join(realpath(sys_get_temp_dir()), bin2hex(random_bytes(10)));
        @mkdir($tempDir);

        self::assertNotSame(InTempSolutionMapper::mapFile($filePath), InTempSolutionMapper::mapFile($filePath));
        self::assertNotSame(InTempSolutionMapper::mapDirectory($tempDir), InTempSolutionMapper::mapDirectory($tempDir));
    }
}
