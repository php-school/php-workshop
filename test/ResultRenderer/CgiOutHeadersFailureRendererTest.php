<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\CgiOutHeadersFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutHeadersFailureRenderer;

/**
 * Class CgiOutHeadersFailureRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutHeadersFailureRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new CgiOutHeadersFailureRenderer;
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRender()
    {
        $failure = new CgiOutHeadersFailure(
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new CgiOutHeadersFailureRenderer;

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n";
        $expected .= "  [31mheader2: val[0m\n\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }
}
