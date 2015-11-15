<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * 
 * Class CgiOutFailureTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutRequestFailureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CheckInterface
     */
    private $check;

    public function setUp()
    {
        $this->check = $this->getMock(CheckInterface::class);
        $this->check
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Some Check'));
        
        $request = $this->getMock(RequestInterface::class);
        $cgiOutResult = new CgiOutRequestFailure($this->check, $request, '', '', [], []);
        $this->assertSame('Some Check', $cgiOutResult->getCheckName());
        $this->assertSame($request, $cgiOutResult->getRequest());
    }
    
    public function testWhenOnlyOutputDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->check, 
            $this->getMock(RequestInterface::class), 
            'Expected Output', 
            'Actual Output', 
            [], 
            []
        );
        
        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
        $this->assertTrue($failure->bodyDifferent());
        $this->assertFalse($failure->headersDifferent());
        $this->assertSame($failure->getExpectedHeaders(), $failure->getActualHeaders());
    }

    public function testWhenOnlyHeadersDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->check,
            $this->getMock(RequestInterface::class),
            'Output',
            'Output',
            ['header1' => 'some-value'],
            ['header2' => 'some-value']
        );
        
        $this->assertEquals(['header1' => 'some-value'], $failure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $failure->getActualHeaders());
        $this->assertTrue($failure->headersDifferent());
        $this->assertFalse($failure->bodyDifferent());
        $this->assertSame($failure->getExpectedOutput(), $failure->getActualOutput());
    }

    public function testWhenOutputAndHeadersDifferent()
    {
        $failure = new CgiOutRequestFailure(
            $this->check,
            $this->getMock(RequestInterface::class),
            'Expected Output',
            'Actual Output',
            ['header1' => 'some-value'],
            ['header2' => 'some-value']
        );
        
        $this->assertTrue($failure->headersDifferent());
        $this->assertTrue($failure->bodyDifferent());
        
        $this->assertEquals(['header1' => 'some-value'], $failure->getExpectedHeaders());
        $this->assertEquals(['header2' => 'some-value'], $failure->getActualHeaders());
        $this->assertNotEquals($failure->getExpectedHeaders(), $failure->getActualHeaders());

        $this->assertEquals('Expected Output', $failure->getExpectedOutput());
        $this->assertEquals('Actual Output', $failure->getActualOutput());
        $this->assertNotEquals($failure->getExpectedOutput(), $failure->getActualOutput());
    }
}
