<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;
use PhpSchool\PhpWorkshop\Utils\Path;
use PhpSchool\PhpWorkshopTest\BaseTest;

class SolutionFileTest extends BaseTest
{
    public function testExceptionIsThrowIfFileNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File: "file/that/does/not/exist.php" does not exist');

        SolutionFile::fromFile('file/that/does/not/exist.php');
    }

    public function testPaths(): void
    {
        $filePath = $this->getTemporaryFile('test.file');

        $file = SolutionFile::fromFile($filePath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($this->getTemporaryDirectory(), $file->getBaseDirectory());
    }

    public function testEmptyContents(): void
    {
        $filePath = $this->getTemporaryFile('test.file');

        $file = SolutionFile::fromFile($filePath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($this->getTemporaryDirectory(), $file->getBaseDirectory());
        $this->assertSame('', $file->getContents());
    }

    public function testGetContents(): void
    {
        $filePath = $this->getTemporaryFile('test.file', 'epiccontentz');

        $file = SolutionFile::fromFile($filePath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($this->getTemporaryDirectory(), $file->getBaseDirectory());
        $this->assertSame('epiccontentz', $file->getContents());
    }

    public function testConstructionWithManualBaseDirectory(): void
    {
        $tempPath = Path::join($this->getTemporaryDirectory(), 'sub-dir');
        $filePath = $this->getTemporaryFile('sub-dir/test.file', 'epiccontentz');
        $file = new SolutionFile('test.file', $tempPath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($tempPath, $file->getBaseDirectory());
        $this->assertSame('epiccontentz', $file->getContents());
    }


    public function testGetExtension(): void
    {
        $tempPath = Path::join($this->getTemporaryDirectory(), 'sub-dir');
        $this->getTemporaryFile('sub-dir/test.php', 'epiccontentz');

        $file = new SolutionFile('test.php', $tempPath);
        $this->assertSame('php', $file->getExtension());
    }
}
