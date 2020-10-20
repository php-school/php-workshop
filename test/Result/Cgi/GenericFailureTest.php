<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cgi;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Cgi\GenericFailure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class GenericFailureTest extends TestCase
{
    public function testFailure(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $failure = new GenericFailure($request, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertSame($request, $failure->getRequest());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CGI Program Runner', $failure->getCheckName());
    }

    public function testFailureWithRequestAndReason(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $failure = GenericFailure::fromRequestAndReason($request, 'Oops');
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertSame($request, $failure->getRequest());
        $this->assertEquals('Oops', $failure->getReason());
        $this->assertEquals('CGI Program Runner', $failure->getCheckName());
    }

    public function testFailureFromCodeExecutionException(): void
    {
        $e = new CodeExecutionException('Something went wrong yo');
        $request = $this->createMock(RequestInterface::class);
        $failure = GenericFailure::fromRequestAndCodeExecutionFailure($request, $e);
        $this->assertInstanceOf(GenericFailure::class, $failure);
        $this->assertSame($request, $failure->getRequest());
        $this->assertEquals('Something went wrong yo', $failure->getReason());
        $this->assertEquals('CGI Program Runner', $failure->getCheckName());
    }
}
