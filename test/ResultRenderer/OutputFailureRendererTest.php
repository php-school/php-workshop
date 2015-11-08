<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use InvalidArgumentException;
use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\Result\StdOutFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\OutputFailureRenderer;

/**
 * Class OutputFailureRendererTest
 * @package PhpSchool\PhpWorkshopTest\ResultRenderer
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class OutputFailureRendererTest extends AbstractResultRendererTest
{
    public function testRendererThrowsExceptionIfNotCorrectResult()
    {
        $mock = $this->getMock(ResultInterface::class);
        $this->setExpectedException(
            InvalidArgumentException::class,
            sprintf('Incompatible result type: %s', get_class($mock))
        );
        $renderer = new OutputFailureRenderer;
        $renderer->render($mock, $this->getRenderer());
    }

    public function testRender()
    {
        $failure = new StdOutFailure('EXPECTED OUTPUT', 'ACTUAL OUTPUT');
        $renderer = new OutputFailureRenderer;

        $expected  = "  [33m[4m[1mACTUAL[0m[0m[0m\n";
        $expected .= "  [31m\"ACTUAL OUTPUT\"[0m\n\n";
        $expected .= "  [4m[1m[33mEXPECTED[0m[0m[0m\n";
        $expected .= "  [31m\"EXPECTED OUTPUT\"[0m\n";

        $this->assertEquals($expected, $renderer->render($failure, $this->getRenderer()));
    }
}
