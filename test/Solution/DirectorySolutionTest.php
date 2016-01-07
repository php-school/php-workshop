<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Solution\DirectorySolution;
use PHPUnit_Framework_TestCase;

/**
 * Class DirectorySolutionTest
 * @package PhpSchool\PhpWorkshopTest\Solution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 * @author Michael Woodward <mikeymike.mw@gmail.com>
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

        $solution = DirectorySolution::fromDirectory($tempPath, [], 'index.php');

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
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[1]->__toString());

        unlink(sprintf('%s/composer.lock', $tempPath));
        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
    }

    public function testWithExceptions()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);
        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/exclude.txt', $tempPath));

        $exclusions = ['exclude.txt'];

        $solution = DirectorySolution::fromDirectory($tempPath, $exclusions);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[1]->__toString());

        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/exclude.txt', $tempPath));
        rmdir($tempPath);
    }

    public function testWithNestedDirectories()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);

        @mkdir(sprintf('%s/nested', $tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $tempPath), 0775, true);

        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/composer.json', $tempPath));
        touch(sprintf('%s/nested/another-class.php', $tempPath));
        touch(sprintf('%s/nested/deep/even-more.php', $tempPath));

        $solution = DirectorySolution::fromDirectory($tempPath);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(5, $files);

        $this->assertSame(sprintf('%s/composer.json', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/nested/another-class.php', $tempPath), $files[1]->__toString());
        $this->assertSame(sprintf('%s/nested/deep/even-more.php', $tempPath), $files[2]->__toString());
        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[3]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[4]->__toString());

        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/composer.json', $tempPath));
        unlink(sprintf('%s/nested/another-class.php', $tempPath));
        unlink(sprintf('%s/nested/deep/even-more.php', $tempPath));
        rmdir(sprintf('%s/nested/deep', $tempPath));
        rmdir(sprintf('%s/nested', $tempPath));
        rmdir($tempPath);
    }

    public function testExceptionsWithNestedDirectories()
    {
        $tempPath = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        @mkdir($tempPath, 0775, true);

        @mkdir(sprintf('%s/nested', $tempPath), 0775, true);
        @mkdir(sprintf('%s/nested/deep', $tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor', $tempPath), 0775, true);
        @mkdir(sprintf('%s/vendor/somelib', $tempPath), 0775, true);

        touch(sprintf('%s/solution.php', $tempPath));
        touch(sprintf('%s/some-class.php', $tempPath));
        touch(sprintf('%s/exclude.txt', $tempPath));
        touch(sprintf('%s/nested/exclude.txt', $tempPath));
        touch(sprintf('%s/nested/deep/exclude.txt', $tempPath));
        touch(sprintf('%s/vendor/somelib/app.php', $tempPath));

        $exclusions = ['exclude.txt', 'vendor'];

        $solution = DirectorySolution::fromDirectory($tempPath, $exclusions);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $solution->getEntryPoint());
        $this->assertInternalType('array', $solution->getFiles());
        $files = $solution->getFiles();
        $this->assertCount(2, $files);

        $this->assertSame(sprintf('%s/solution.php', $tempPath), $files[0]->__toString());
        $this->assertSame(sprintf('%s/some-class.php', $tempPath), $files[1]->__toString());

        unlink(sprintf('%s/solution.php', $tempPath));
        unlink(sprintf('%s/some-class.php', $tempPath));
        unlink(sprintf('%s/exclude.txt', $tempPath));
        unlink(sprintf('%s/nested/exclude.txt', $tempPath));
        unlink(sprintf('%s/nested/deep/exclude.txt', $tempPath));
        unlink(sprintf('%s/vendor/somelib/app.php', $tempPath));
        rmdir(sprintf('%s/nested/deep', $tempPath));
        rmdir(sprintf('%s/nested', $tempPath));
        rmdir(sprintf('%s/vendor/somelib', $tempPath));
        rmdir(sprintf('%s/vendor', $tempPath));
        rmdir($tempPath);
    }
}
