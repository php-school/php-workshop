<?php

namespace PhpSchool\PhpWorkshopTest\ResultRenderer\Cgi;

use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\ResultRenderer\Cgi\RequestFailureRenderer;
use PhpSchool\PhpWorkshopTest\ResultRenderer\AbstractResultRendererTest;
use Zend\Diactoros\Request;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RequestFailureRendererTest extends AbstractResultRendererTest
{
    public function testRenderWhenOnlyHeadersDifferent(): void
    {
        $failure = new RequestFailure(
            $this->request(),
            'OUTPUT',
            'OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new RequestFailureRenderer($failure);

        $expected  = "\e[33m\e[1mYOUR HEADERS:\e[0m\e[0m      \e[31mheader1: val\e[0m\n";
        $expected .= "\n";
        $expected .= "\e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[32mheader1: val\e[0m\n";
        $expected .= "                   \e[32mheader2: val\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWhenOnlyOutputDifferent(): void
    {
        $failure = new RequestFailure(
            $this->request(),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new RequestFailureRenderer($failure);

        $expected  = "\e[33m\e[1mYOUR OUTPUT:\e[0m\e[0m       \e[31m\"ACTUAL OUTPUT\"\e[0m\n";
        $expected .= "\n";
        $expected .= "\e[33m\e[1mEXPECTED OUTPUT:\e[0m\e[0m   \e[32m\"EXPECTED OUTPUT\"\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    public function testRenderWhenOutputAndHeadersDifferent(): void
    {
        $failure = new RequestFailure(
            $this->request(),
            'EXPECTED OUTPUT',
            'ACTUAL OUTPUT',
            ['header1' => 'val', 'header2' => 'val'],
            ['header1' => 'val']
        );
        $renderer = new RequestFailureRenderer($failure);

        $expected  = "\e[33m\e[1mYOUR HEADERS:\e[0m\e[0m      \e[31mheader1: val\e[0m\n";
        $expected .= "\n";
        $expected .= "\e[33m\e[1mEXPECTED HEADERS:\e[0m\e[0m  \e[32mheader1: val\e[0m\n";
        $expected .= "                   \e[32mheader2: val\e[0m\n\n";
        $expected .= "[1m[39m- - - - - - - - -[0m[0m\n";
        $expected .= "\n";
        $expected .= "\e[33m\e[1mYOUR OUTPUT:\e[0m\e[0m       \e[31m\"ACTUAL OUTPUT\"\e[0m\n";
        $expected .= "\n";
        $expected .= "\e[33m\e[1mEXPECTED OUTPUT:\e[0m\e[0m   \e[32m\"EXPECTED OUTPUT\"\e[0m\n";

        $this->assertSame($expected, $renderer->render($this->getRenderer()));
    }

    private function request(): Request
    {
        return (new Request('http://www.test.com'))
            ->withMethod('POST')
            ->withHeader('Content-Type', 'application/json');
    }
}
