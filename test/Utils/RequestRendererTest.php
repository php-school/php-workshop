<?php

namespace PhpSchool\PhpWorkshopTest\Utils;

use GuzzleHttp\Psr7\Request;
use PhpSchool\PhpWorkshop\Utils\RequestRenderer;
use PHPUnit\Framework\TestCase;

class RequestRendererTest extends TestCase
{
    public function testWriteRequestWithHeaders(): void
    {
        $request = (new Request('GET', 'http://www.time.com/api/pt?iso=2016-01-21T18:14:33+0000'));

        $expected  = "URL:     http://www.time.com/api/pt?iso=2016-01-21T18:14:33+0000\n";
        $expected .= "METHOD:  GET\n";
        $expected .= "HEADERS: Host: www.time.com\n";

        $this->assertEquals($expected, (new RequestRenderer())->renderRequest($request));
    }

    public function testWriteRequestWithNoHeaders(): void
    {
        $request = (new Request('GET', '/endpoint'));

        $expected  = "URL:     /endpoint\n";
        $expected .= "METHOD:  GET\n";

        $this->assertEquals($expected, (new RequestRenderer())->renderRequest($request));
    }

    public function testWriteRequestWithPostBodyJson(): void
    {
        $request = (new Request('POST', '/endpoint'))
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write(
            json_encode(['data' => 'test', 'other_data' => 'test2'])
        );

        $expected  = "URL:     /endpoint\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Content-Type: application/json\n";
        $expected .= "BODY:    {\n";
        $expected .= "    \"data\": \"test\",\n";
        $expected .= "    \"other_data\": \"test2\"\n";
        $expected .= "}\n";

        $this->assertEquals($expected, (new RequestRenderer())->renderRequest($request));
    }

    public function testWriteRequestWithPostBodyUrlEncoded(): void
    {
        $request = (new Request('POST', '/endpoint'))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write(
            http_build_query(['data' => 'test', 'other_data' => 'test2'])
        );

        $expected  = "URL:     /endpoint\n";
        $expected .= "METHOD:  POST\n";
        $expected .= "HEADERS: Content-Type: application/x-www-form-urlencoded\n";
        $expected .= "BODY:    data=test&other_data=test2\n";

        $this->assertEquals($expected, (new RequestRenderer())->renderRequest($request));
    }
}
