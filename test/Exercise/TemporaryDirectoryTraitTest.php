<?php

namespace PhpSchool\PhpWorkshopTest\Exercise;

use PhpSchool\PhpWorkshopTest\Asset\TemporaryDirectoryTraitImpl;
use PHPUnit_Framework_TestCase;

/**
 * Class TemporaryDirectoryTraitTest
 * @package PhpSchool\PhpWorkshopTest\Exercise
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TemporaryDirectoryTraitTest extends PHPUnit_Framework_TestCase
{
    public function testGetTemporaryPath()
    {
        $impl = new TemporaryDirectoryTraitImpl;
        $path = $impl->getTemporaryPath();
        $this->assertInternalType('string', $path);
        
        mkdir($path, 0775, true);
        $this->assertFileExists($path);
        rmdir($path);
    }
}
