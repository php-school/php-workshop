<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer;

use GuzzleHttp\Psr7\Request;
use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PhpSchool\PhpWorkshop\ResultRenderer\Cgi\RequestFailureRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\CgiResultRenderer;
use PhpSchool\PhpWorkshop\ResultRenderer\FailureRenderer;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;

class CgiResultRendererTest extends AbstractResultRendererTest
{
    public function testNothingIsOutputIfNoFailures(): void
    {
        $result = new CgiResult([new Success($this->request())]);
        $renderer = new CgiResultRenderer($result, new RequestRenderer());

        $this->assertEmpty($renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequest(): void
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
            $this->request(),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiResult([$failure]);
        $renderer = new CgiResultRenderer($result, new RequestRenderer());

        $expected  = "Some requests to your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n\n";
        $expected .= "REQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testMultipleFailedRequests(): void
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

        $failure1 = new RequestFailure(
            $this->request(),
            'EXPECTED OUTPUT 1',
            'ACTUAL OUTPUT 1',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );

        $failure2 = new RequestFailure(
            $this->request(),
            'EXPECTED OUTPUT 2',
            'ACTUAL OUTPUT 2',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiResult([$failure1, $failure2]);
        $renderer = new CgiResultRenderer($result, new RequestRenderer());

        $expected  = "Some requests to your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n";
        $expected .= "\n";
        $expected .= "REQUEST FAILURE\n\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 2\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n";
        $expected .= "\n";
        $expected .= "REQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequestAndSuccess(): void
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
            $this->request(),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $result = new CgiResult([$failure, new Success($this->request())]);
        $renderer = new CgiResultRenderer($result, new RequestRenderer());

        $expected  = "Some requests to your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n\n";
        $expected .= "REQUEST FAILURE\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWithFailedRequestAndGenericFailure(): void
    {

        $requestFailureRenderer = $this->prophesize(RequestFailureRenderer::class);
        $requestFailureRenderer->render($this->getRenderer())->willReturn("REQUEST FAILURE\n");

        $this->getResultRendererFactory()->registerRenderer(
            RequestFailure::class,
            RequestFailureRenderer::class,
            function (RequestFailure $failure) use ($requestFailureRenderer) {
                return $requestFailureRenderer->reveal();
            }
        );

        $genericFailureRenderer = $this->prophesize(FailureRenderer::class);
        $genericFailureRenderer->render($this->getRenderer())->willReturn("Code Execution Failure\n");

        $this->getResultRendererFactory()->registerRenderer(
            GenericFailure::class,
            FailureRenderer::class,
            function (GenericFailure $failure) use ($genericFailureRenderer) {
                return $genericFailureRenderer->reveal();
            }
        );

        $failure = new RequestFailure(
            $this->request(),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );

        $codeExecutionFailure = new GenericFailure($this->request(), 'Code Execution Failure');
        $result = new CgiResult([$failure, $codeExecutionFailure]);
        $renderer = new CgiResultRenderer($result, new RequestRenderer());

        $expected  = "Some requests to your solution produced incorrect output!\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 1\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n";
        $expected .= "\n";
        $expected .= "REQUEST FAILURE\n\n";
        $expected .= "\e[33m──────────────────────────────────────────────────\e[0m\n";
        $expected .= "\e[34m\e[4m\e[1mRequest 2\e[0m\e[0m\e[0m \e[1m\e[41m FAILED \e[0m\e[0m\n";
        $expected .= "\n";
        $expected .= "Request Details:\n";
        $expected .= "\n";
        $expected .= "URL:     http://www.test.com\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Host: www.test.com\n";
        $expected .= "         Content-Type: application/json\n";
        $expected .= "\n";
        $expected .= "Code Execution Failure\n\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    private function request(): Request
    {
        return (new Request('POST', 'http://www.test.com'))
            ->withHeader('Content-Type', 'application/json');
    }
}
