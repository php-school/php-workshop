<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\FileComparisonFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\FileComparisonFailureRenderer;

class FileComparisonFailureRendererTest extends AbstractResultRendererTest
{
    public function testRender(): void
    {
        $failure  = new FileComparisonFailure(
            $this->createMock(CheckInterface::class),
            'some-file.text',
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
        );
        $renderer = new FileComparisonFailureRenderer($failure);

        $expected  = "  \e[33m\e[1mYOUR OUTPUT FOR: \e[0m\e[0m\e[32m\e[1msome-file.text\e[0m\e[0m\n";
        $expected .= "  \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED OUTPUT FOR: \e[0m\e[0m\e[32m\e[1msome-file.text\e[0m\e[0m\n";
        $expected .= "  \e[32m\"EXPECTED OUTPUT\"\e[0m\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }
}
