<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Result\CgiOutBodyFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutRequestFailure;
use PhpSchool\PhpWorkshop\Result\CgiOutResult;
use PhpSchool\PhpWorkshop\Result\Success;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * 
 * Class CgiOutResultTest
 * @package PhpSchool\PhpWorkshopTest\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResultTest extends PHPUnit_Framework_TestCase
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
        
        $cgiOutResult = new CgiOutResult($this->check, []);
        $this->assertSame('Some Check', $cgiOutResult->getCheckName());
    }

    public function testIsSuccessful()
    {
        $request = new CgiOutRequestFailure($this->check, $this->getMock(RequestInterface::class), '', '', [], []);
        $cgiOutResult = new CgiOutResult($this->check, [$request]);
        
        $this->assertFalse($cgiOutResult->isSuccessful());

        $cgiOutResult = new CgiOutResult($this->check, [new Success($this->check)]);
        $this->assertTrue($cgiOutResult->isSuccessful());
        
        $cgiOutResult->add($request);
        $this->assertFalse($cgiOutResult->isSuccessful());
    }   
}
