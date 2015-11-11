<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiOutFailureRenderer;

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
        $renderer = new CgiOutFailureRenderer;
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRenderWhenOnlyHeadersDifferent()
    {
        $failure = new CgiOutFailure(
            $this->getMock(CheckInterface::class),
            'OUTPUT',
            'OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new CgiOutFailureRenderer;

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n";
        $expected .= "  [31mheader2: val[0m\n\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }

    public function testRenderWhenOnlyOutputDifferent()
    {
        $failure = new CgiOutFailure(
            $this->getMock(CheckInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new CgiOutFailureRenderer;

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31m\"ACTUAL OUTPUT\"[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31m\"EXPECTED OUTPUT\"[0m\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }

    public function testRenderWhenOutputAndHeadersDifferent()
    {
        $failure = new CgiOutFailure(
            $this->getMock(CheckInterface::class),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new CgiOutFailureRenderer;

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31mheader1: val[0m\n";
        $expected .= "  [31mheader2: val[0m\n\n\n";
        $expected .= "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31m\"ACTUAL OUTPUT\"[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31m\"EXPECTED OUTPUT\"[0m\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }
}
