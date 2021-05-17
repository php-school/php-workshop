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
        file_put_contents($filePath, 'FILE CONTENTS');

        $solution = SingleFileSolution::fromFile($filePath);

        self::assertSame('FILE CONTENTS', file_get_contents($solution->getEntryPoint()));
        self::assertFalse($solution->hasComposerFile());
        self::assertCount(1, $solution->getFiles());
        self::assertSame('FILE CONTENTS', file_get_contents($solution->getFiles()[0]->__toString()));
        unlink($filePath);
        rmdir($tempPath);
    }
}
