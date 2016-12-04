<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\ResultRenderer\Cli\RequestFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CliResultRenderer;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliResultRendererTest extends AbstractResultRendererTest
{
    public function testNothingIsOutputIfNoFailures()
    {
        $result = new CliResult([new Success(new ArrayObject)]);
        $renderer = new CliResultRenderer($result, new RequestRenderer);

        $this->assertEmpty($renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequest()
    {
        $failureRenderer = $this->prophesize(RequestFailureRenderer::class);
        $failureRenderer->render($this->getRenderer())->willReturn("REQUEST FAILURE\n");

        $this->getResultRendererFactory()->registerRenderer(
            RequestFailure::class,
            RequestFailureRenderer::class,
            function (RequestFailure $failure) use ($failureRenderer) {
                return $failureRenderer->reveal();
            }
        );

        $failure = new RequestFailure(
            new ArrayObject,
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT'
        );
        $result = new CliResult([$failure]);
        $renderer = new CliResultRenderer($result, new RequestRenderer);

        $expected = "REQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }
}
