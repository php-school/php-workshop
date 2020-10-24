<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cgi;

use PhpSchool\PhpWorkshop\Result\Cgi\CgiResult;
use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class CgiResultTest extends TestCase
{
    public function testName(): void
    {
        $request = new RequestFailure($this->createMock(RequestInterface::class), '', '', [], []);
        $cgiResult = new CgiResult([$request]);
        $this->assertSame('CGI Program Runner', $cgiResult->getCheckName());
    }

    public function testIsSuccessful(): void
    {
        $request = new RequestFailure($this->createMock(RequestInterface::class), '', '', [], []);
        $cgiResult = new CgiResult([$request]);

        $this->assertFalse($cgiResult->isSuccessful());

        $cgiResult = new CgiResult([new Success($this->createMock(RequestInterface::class))]);
        $this->assertTrue($cgiResult->isSuccessful());

        $cgiResult->add($request);
        $this->assertFalse($cgiResult->isSuccessful());
    }
}
