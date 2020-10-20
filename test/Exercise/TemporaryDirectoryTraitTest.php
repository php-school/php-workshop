<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshopTest\Asset\TemporaryDirectoryTraitImpl;
use PHPUnit\Framework\TestCase;

class TemporaryDirectoryTraitTest extends TestCase
{
    public function testGetTemporaryPath(): void
    {
        $impl = new TemporaryDirectoryTraitImpl();
        $path = $impl->getTemporaryPath();

        mkdir($path, 0775, true);
        $this->assertFileExists($path);
        rmdir($path);
    }
}
