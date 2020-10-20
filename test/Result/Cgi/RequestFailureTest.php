<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cgi;

use PhpSchool\PhpWorkshop\Result\Cgi\RequestFailure;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class RequestFailureTest extends TestCase
{
    public function setUp(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $requestFailure = new RequestFailure($request, '', '', [], []);
        $this->assertSame('Request Failure', $requestFailure->getCheckName());
        $this->assertSame($request, $requestFailure->getRequest());
    }
    
    public function testWhenOnlyOutputDifferent(): void
    {
        $requestFailure = new RequestFailure(
            $this->createMock(RequestInterface::class),
            'Expected Output',
            'Actual Output',
            [],
            []
        );
        
        $this->assertEquals('Expected Output', $requestFailure->getExpectedOutput());
        $this->assertEquals('Actual Output', $requestFailure->getActualOutput());
        $this->assertTrue($requestFailure->bodyDifferent());
        $this->assertFalse($requestFailure->headersDifferent());
        $this->assertFalse($requestFailure->headersAndBodyDifferent());
        $this->assertSame($requestFailure->getExpectedHeaders(), $requestFailure->getActualHeaders());
    }

    public function testWhenOnlyHeadersDifferent(): void
    {
        $requestFailure = new RequestFailure(
            $this->createMock(RequestInterface::class),
            'Output',
            'Output',
            ['header1' => 'some-value'],
            ['header2' => 'some-value']
        );
        
        $this->assertEquals(['header1' => 'some-value'], $requestFailure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $requestFailure->getActualHeaders());
        $this->assertTrue($requestFailure->headersDifferent());
        $this->assertFalse($requestFailure->bodyDifferent());
        $this->assertFalse($requestFailure->headersAndBodyDifferent());
        $this->assertSame($requestFailure->getExpectedOutput(), $requestFailure->getActualOutput());
    }

    public function testWhenOutputAndHeadersDifferent(): void
    {
        $requestFailure = new RequestFailure(
            $this->createMock(RequestInterface::class),
            'Expected Output',
            'Actual Output',
            ['header1' => 'some-value'],
            ['header2' => 'some-value']
        );
        
        $this->assertTrue($requestFailure->headersDifferent());
        $this->assertTrue($requestFailure->bodyDifferent());
        $this->assertTrue($requestFailure->headersAndBodyDifferent());

        $this->assertEquals(['header1' => 'some-value'], $requestFailure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $requestFailure->getActualHeaders());
        $this->assertNotEquals($requestFailure->getExpectedHeaders(), $requestFailure->getActualHeaders());

        $this->assertEquals('Expected Output', $requestFailure->getExpectedOutput());
        $this->assertEquals('Actual Output', $requestFailure->getActualOutput());
        $this->assertNotEquals($requestFailure->getExpectedOutput(), $requestFailure->getActualOutput());
    }
}
