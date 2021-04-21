<?php

namespace PhpSchool\PhpWorkshopTest\Util\s;

use PhpSchool\PhpWorkshop\Utils\Path;
use PHPUnit\Framework\TestCase;

class PathTest extends TestCase
{
    public function testJoin(): void
    {
        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path', 'some-folder/file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path/', 'some-folder/file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path', '/some-folder/file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path/', '/some-folder/file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path//', '//some-folder/file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path/', 'some-folder', 'file.txt')
        );

        $this->assertEquals(
            '/some/path/some-folder/file.txt',
            Path::join('/some/path/', '/some-folder/', '/file.txt')
        );
    }
}
