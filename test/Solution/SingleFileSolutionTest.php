<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PHPUnit_Framework_TestCase;

/**
 * Class SingleFileSolutionTest
 * @package PhpSchool\PhpWorkshop\Solution
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SingleFileSolutionTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $tempPath   = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $solution = SingleFileSolution::fromFile($filePath);
        
        $this->assertSame($filePath, $solution->getEntryPoint());
        $this->assertSame($tempPath, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertInternalType('array', $solution->getFiles());
        $this->assertCount(1, $solution->getFiles());
        $this->assertSame($filePath, $solution->getFiles()[0]->__toString());
        unlink($filePath);
        rmdir($tempPath);
    }
}
