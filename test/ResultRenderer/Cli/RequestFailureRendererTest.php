<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer\Cli;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\Cli\RequestFailureRenderer;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshopTest\ResultRenderer\AbstractResultRendererTest;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RequestFailureRendererTest extends AbstractResultRendererTest
{
    public function testRender()
    {
        $failure  = new RequestFailure(new ArrayObject, 'EXPECTED OUTPUT', 'ACTUAL OUTPUT');
        $renderer = new RequestFailureRenderer($failure);

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31m\"ACTUAL OUTPUT\"[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31m\"EXPECTED OUTPUT\"[0m\n";

        $this->assertEquals($expected, $renderer->render($this->getRenderer()));
    }
}
