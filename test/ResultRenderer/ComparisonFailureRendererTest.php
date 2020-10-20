<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\ComparisonFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\ComparisonFailureRenderer;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ComparisonFailureRendererTest extends AbstractResultRendererTest
{
    public function testRender(): void
    {
        $failure  = new ComparisonFailure('Name', 'EXPECTED OUTPUT', 'ACTUAL OUTPUT');
        $renderer = new ComparisonFailureRenderer($failure);

        $expected  = "  \e[33m\e[1mYOUR OUTPUT:\e[0m\e[0m\n";
        $expected .= "  \e[31m\"ACTUAL OUTPUT\"\e[0m\n\n";
        $expected .= "  \e[33m\e[1mEXPECTED OUTPUT:\e[0m\e[0m\n";
        $expected .= "  \e[32m\"EXPECTED OUTPUT\"\e[0m\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }
}
