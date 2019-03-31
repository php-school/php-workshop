<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshopTest\Asset\TemporaryDirectoryTraitImpl;
use PHPUnit\Framework\TestCase;

/**
 * Class TemporaryDirectoryTraitTest
 * @package PhpSchool\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TemporaryDirectoryTraitTest extends TestCase
{
    public function testGetTemporaryPath() : void
    {
        $impl = new TemporaryDirectoryTraitImpl;
        $path = $impl->getTemporaryPath();
        $this->assertInternalType('string', $path);
        
        mkdir($path, 0775, true);
        $this->assertFileExists($path);
        rmdir($path);
    }
}
