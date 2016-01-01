<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectorySolutionTest
 * @package PhpSchool\PhpWorkshopTest\Solution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class DirectorySolutionTest extends PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfEntryPointDoesNotExist()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/some-class.php', $tempPath));
        
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Entry point: "solution.php" does not exist in: "%s"', $tempPath)
        );
        
        DirectorySolution::fromDirectory($tempPath);

        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
    }

    public function testWithDefaultEntryPoint()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        
        $solution = DirectorySolution::fromDirectory($tempPath);
        
        $this->assertSame($tempPath, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/solution.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);
        
        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[1]->__toString());
        
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
    }

    public function testWithManualEntryPoint()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/index.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath, 'index.php');

        $this->assertSame($tempPath, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/index.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/index.php', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[1]->__toString());

        unlink(sprintf('%s/index.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
    }

    public function testHasComposerFileReturnsTrueIfPresent()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/composer.lock', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath);

        $this->assertSame($tempPath, $solution->getBaseDirectory());
        $this->assertTrue($solution->hasComposerFile());
        $this->assertSame(sprintf('%s/solution.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(3, $files);

        $this->assertSame(sprintf('%s/composer.lock', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[1]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[2]->__toString());

        unlink(sprintf('%s/composer.lock', $tempPath));
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        rmdir($tempPath);
    }
}
