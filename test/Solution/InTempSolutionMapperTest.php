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

    public function testMappingIsDeterministicTempDir(): void
    {
        $filePath  = Path::join(realpath(sys_get_temp_dir()), 'test.file');
        touch($filePath);

        $dirName = bin2hex(random_bytes(10));
        $tempDir = Path::join(realpath(sys_get_temp_dir()), $dirName);
        @mkdir($tempDir);

        $fileHash = md5($filePath);
        $dirHash = md5($tempDir);

        self::assertSame(
            InTempSolutionMapper::mapFile($filePath),
            Path::join(realpath(sys_get_temp_dir()), 'php-school', $fileHash, 'test.file')
        );

        self::assertNotSame(
            InTempSolutionMapper::mapDirectory($tempDir),
            Path::join(realpath(sys_get_temp_dir()), 'php-school', $dirHash, $dirName)
        );
    }

    public function testContentsAreNotOverwroteIfExists(): void
    {
        $filePath = Path::join(realpath(sys_get_temp_dir()), 'test.file');
        file_put_contents($filePath, 'Old contents');

        $dirName = bin2hex(random_bytes(10));
        $tempDir = Path::join(realpath(sys_get_temp_dir()), $dirName);
        mkdir($tempDir);
        file_put_contents(Path::join($tempDir, 'test.file'), 'Old contents');

        $tempFilePath = Path::join(realpath(sys_get_temp_dir()), 'php-school', md5($filePath), 'test.file');
        $tempDirPath = Path::join(realpath(sys_get_temp_dir()), 'php-school', md5($tempDir), $dirName);

        file_put_contents($tempFilePath, 'Fresh contents');
        mkdir($tempDirPath, 0777, true);
        file_put_contents(Path::join($tempDirPath, 'test.file'), 'Fresh contents');

        // These calls will invoke the copying of of dir/files to temp
        InTempSolutionMapper::mapFile($filePath);
        InTempSolutionMapper::mapDirectory($tempDir);

        self::assertSame('Old contents', file_get_contents($filePath));
        self::assertSame('Fresh contents', file_get_contents($tempFilePath));
        self::assertSame('Old contents', file_get_contents(Path::join($tempDir, 'test.file')));
        self::assertSame('Fresh contents', file_get_contents(Path::join($tempDirPath, 'test.file')));
    }
}
