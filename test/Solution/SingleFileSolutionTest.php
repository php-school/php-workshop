<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PHPUnit\Framework\TestCase;

class SingleFileSolutionTest extends TestCase
{
    public function testGetters(): void
    {
        $tempPath   = sprintf('%s/%s', realpath(sys_get_temp_dir()), $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $solution = SingleFileSolution::fromFile($filePath);
        
        $this->assertSame($filePath, $solution->getEntryPoint());
        $this->assertSame($tempPath, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertCount(1, $solution->getFiles());
        $this->assertSame($filePath, $solution->getFiles()[0]->__toString());
        unlink($filePath);
        rmdir($tempPath);
    }
}
