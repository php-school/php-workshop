<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\SolutionFile;
use PHPUnit\Framework\TestCase;

/**
 * Class SolutionFileTest
 * @package PhpSchool\PhpWorkshop\Solution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SolutionFileTest extends TestCase
{
    public function testExceptionIsThrowIfFileNotExists() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File: "file/that/does/not/exist.php" does not exist');
        
        SolutionFile::fromFile('file/that/does/not/exist.php');
    }

    public function testPaths() : void
    {
        $tempPath   = sprintf('%s/%s', sys_get_temp_dir(), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);
        
        $file = SolutionFile::fromFile($filePath);
        
        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($tempPath, $file->getBaseDirectory());
        
        unlink($filePath);
        rmdir($tempPath);
    }

    public function testEmptyContents() : void
    {
        $tempPath   = sprintf('%s/%s', sys_get_temp_dir(), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $file = SolutionFile::fromFile($filePath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($tempPath, $file->getBaseDirectory());
        $this->assertSame('', $file->getContents());
        unlink($filePath);
        rmdir($tempPath);
    }

    public function testGetContents() : void
    {
        $tempPath   = sprintf('%s/%s', sys_get_temp_dir(), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        file_put_contents($filePath, 'epiccontentz');

        $file = SolutionFile::fromFile($filePath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($tempPath, $file->getBaseDirectory());
        $this->assertSame('epiccontentz', $file->getContents());
        unlink($filePath);
        rmdir($tempPath);
    }

    public function testConstructionWithManualBaseDirectory() : void
    {
        $tempPath   = sprintf('%s/%s/sub-dir', sys_get_temp_dir(), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $file = new SolutionFile('test.file', $tempPath);

        $this->assertSame($filePath, $file->__toString());
        $this->assertSame('test.file', $file->getRelativePath());
        $this->assertSame($tempPath, $file->getBaseDirectory());
        $this->assertSame('', $file->getContents());
        unlink($filePath);
        rmdir($tempPath);
    }

    public function testGetExtension() : void
    {
        $tempPath   = sprintf('%s/%s/sub-dir', sys_get_temp_dir(), $this->getName());
        $filePath   = sprintf('%s/test.php', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $file = new SolutionFile('test.php', $tempPath);
        $this->assertSame('php', $file->getExtension());
    }
}
