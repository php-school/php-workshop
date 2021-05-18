<?php

namespace PhpSchool\PhpWorkshopTest\Solution;

use PhpSchool\PhpWorkshop\Solution\SingleFileSolution;
use PhpSchool\PhpWorkshopTest\BaseTest;

class SingleFileSolutionTest extends BaseTest
{
    public function testGetters(): void
    {
        $filePath = $this->getTemporaryFile('test.file', 'FILE CONTENTS');

        $solution = SingleFileSolution::fromFile($filePath);

        self::assertSame('FILE CONTENTS', file_get_contents($solution->getEntryPoint()));
        self::assertFalse($solution->hasComposerFile());
        self::assertCount(1, $solution->getFiles());
        self::assertSame('FILE CONTENTS', file_get_contents($solution->getFiles()[0]->__toString()));
    }
}
