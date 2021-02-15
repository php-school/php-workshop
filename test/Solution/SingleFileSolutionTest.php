<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshop\TestUtils\SolutionPathTransformer;
use PHPUnit\Framework\TestCase;

class SingleFileSolutionTest extends TestCase
{
    public function testGetters(): void
    {
        $tmpDir = realpath(sys_get_temp_dir());
        $tempPath   = sprintf('%s/%s', $tmpDir, $this->getName());
        $filePath   = sprintf('%s/test.file', $tempPath);

        @mkdir($tempPath, 0775, true);
        touch($filePath);

        $solution = SingleFileSolution::fromFile($filePath);

        $expectedBaseDir = SolutionPathTransformer::tempPathToSolutionTempPath($tempPath);
        $expectedFilePath = SolutionPathTransformer::tempPathToSolutionTempPath($filePath);

        $this->assertSame($expectedFilePath, $solution->getEntryPoint());
        $this->assertSame($expectedBaseDir, $solution->getBaseDirectory());
        $this->assertFalse($solution->hasComposerFile());
        $this->assertCount(1, $solution->getFiles());
        $this->assertSame($expectedFilePath, $solution->getFiles()[0]->__toString());
        unlink($filePath);
        rmdir($tempPath);
    }
}
