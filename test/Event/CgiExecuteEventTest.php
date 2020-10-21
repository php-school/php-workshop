<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use GuzzleHttp\Psr7\Request;
use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class CgiExecuteEventTest extends TestCase
{
    public function testAddHeader(): void
    {
        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $request);

        $e->addHeaderToRequest('Content-Type', 'text/html');
        $this->assertSame(
            [
                'Host' => ['some.site'],
                'Content-Type' => ['text/html'],
            ],
            $e->getRequest()->getHeaders()
        );
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testModifyRequest(): void
    {
        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $request);

        $e->modifyRequest(function (RequestInterface $request) {
            return $request
                ->withHeader('Content-Type', 'text/html')
                ->withMethod('POST');
        });
        $this->assertSame(
            [
                'Host' => ['some.site'],
                'Content-Type' => ['text/html'],
            ],
            $e->getRequest()->getHeaders()
        );
        $this->assertSame('POST', $e->getRequest()->getMethod());
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testGetRequest(): void
    {
        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $request);

        $this->assertSame($request, $e->getRequest());
    }
}
