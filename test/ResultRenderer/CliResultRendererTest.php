<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use PhpSchool\PhpWorkshop\Result\Cli\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cli\Success;
use PhpSchool\PhpWorkshop\Result\Cli\CliResult;
use PhpSchool\PhpWorkshop\ResultRenderer\Cli\RequestFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CliResultRenderer;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliResultRendererTest extends AbstractResultRendererTest
{
    public function testNothingIsOutputIfNoFailures() : void
    {
        $result = new CliResult([new Success(new ArrayObject)]);
        $renderer = new CliResultRenderer($result);

        $this->assertEmpty($renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequest() : void
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
        $renderer = new CliResultRenderer($result);

        $expected  = "Some executions of your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mExecution 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Arguments: None\n\nREQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequestWithMultipleArgs() : void
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
            new ArrayObject(['one', 'two', 'three']),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT'
        );
        $result = new CliResult([$failure]);
        $renderer = new CliResultRenderer($result);

        $expected  = "Some executions of your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mExecution 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Arguments: \"one\", \"two\", \"three\"\n\nREQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }
}
