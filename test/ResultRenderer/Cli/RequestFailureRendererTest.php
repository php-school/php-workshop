<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\Cli\RequestFailureRenderer;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshopTest\ResultRenderer\AbstractResultRendererTest;

class RequestFailureRendererTest extends AbstractResultRendererTest
{
    public function testRender(): void
    {
        $failure  = new RequestFailure(new ArrayObject(), 'EXPECTED OUTPUT', 'ACTUAL OUTPUT');
        $renderer = new RequestFailureRenderer($failure);

        $expected  = "  \e[33m\e[1mYOUR OUTPUT:\e[0m\e[0m\n";
        $expected .= "  \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED OUTPUT:\e[0m\e[0m\n";
        $expected .= "  \e[32m\"EXPECTED OUTPUT\"\e[0m\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }
}
