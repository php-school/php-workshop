<?php

namespace PhpSchool\PhpWorkshopTest\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
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
    public function testName()
    {
        $request = new CgiOutRequestFailure($this->getMock(RequestInterface::class), '', '', [], []);
        $cgiOutResult = new CgiOutResult('Some Check', [$request]);
        $this->assertSame('Some Check', $cgiOutResult->getCheckName());
    }

    public function testIsSuccessful()
    {
        $request = new CgiOutRequestFailure($this->getMock(RequestInterface::class), '', '', [], []);
        $cgiOutResult = new CgiOutResult('Some Check', [$request]);
        
        $this->assertFalse($cgiOutResult->isSuccessful());

        $cgiOutResult = new CgiOutResult('Some Check', [new Success('Successful Check')]);
        $this->assertTrue($cgiOutResult->isSuccessful());
        
        $cgiOutResult->add($request);
        $this->assertFalse($cgiOutResult->isSuccessful());
    }
}
