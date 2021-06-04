<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Solution;

use PhpSchool\PhpWorkshop\Solution\InTempSolutionMapper;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshop\Utils\System;
use PhpSchool\PhpWorkshopTest\BaseTest;
use Symfony\Component\Filesystem\Filesystem;

class InTempSolutionMapperTest extends BaseTest
{
    public function tearDown(): void
    {
        (new Filesystem())->remove(System::tempDir('php-school'));

        parent::tearDown();
    }

    public function testFileMapping(): void
    {
        $filePath = $this->getTemporaryFile('test.file');

        $mappedFile = InTempSolutionMapper::mapFile($filePath);

        self::assertFileExists($mappedFile);
        self::assertNotSame($filePath, $mappedFile);
        self::assertStringContainsString(System::tempDir(), $mappedFile);
    }

    public function testDirectoryMapping(): void
    {
        $this->getTemporaryFile('test.file');
        $this->getTemporaryFile('innerDir/test.file');

        $mappedDir = InTempSolutionMapper::mapDirectory($this->getTemporaryDirectory());

        self::assertDirectoryExists($mappedDir);
        self::assertDirectoryExists(Path::join($mappedDir, 'innerDir'));
        self::assertFileExists(Path::join($mappedDir, 'test.file'));
        self::assertFileExists(Path::join($mappedDir, 'innerDir', 'test.file'));
        self::assertNotSame($this->getTemporaryDirectory(), $mappedDir);
        self::assertStringContainsString(System::tempDir(), $mappedDir);
    }

    public function testMappingIsDeterministicTempDir(): void
    {
        $filePath = $this->getTemporaryFile('test.file');

        $dirName = bin2hex(random_bytes(10));
        $tempDir = Path::join($this->getTemporaryDirectory(), $dirName);
        mkdir($tempDir);

        $fileHash = md5($filePath);
        $dirHash = md5($tempDir);

        self::assertSame(
            InTempSolutionMapper::mapFile($filePath),
            Path::join(System::tempDir(), $fileHash, 'test.file')
        );

        self::assertNotSame(
            InTempSolutionMapper::mapDirectory($this->getTemporaryDirectory()),
            System::tempDir(Path::join('php-school', $dirHash, dirname($dirName)))
        );
    }

    public function testContentsAreNotOverwroteIfExists(): void
    {
        $filePath = $this->getTemporaryFile('test.file', 'Old contents');

        $dirName = bin2hex(random_bytes(10));
        $tempDir = Path::join($this->getTemporaryDirectory(), $dirName);

        $this->getTemporaryFile(Path::join($dirName, 'test.file'), 'Old contents');

        $tempFilePath = System::tempDir(Path::join('php-school', md5($filePath), 'test.file'));
        $tempDirPath = System::tempDir(Path::join('php-school', md5($tempDir), $dirName));

        mkdir(dirName($tempFilePath), 0777, true);
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
