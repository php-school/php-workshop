<?php

namespace PhpSchool\PhpWorkshopTest\Result\Cgi;

use PhpSchool\PhpWorkshop\Result\Cgi\Success;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class SuccessTest extends PHPUnit_Framework_TestCase
{
    public function testSuccess()
    {
        $request = $this->createMock(RequestInterface::class);
        $success = new Success($request);
        $this->assertInstanceOf(Success::class, $success);
        $this->assertSame($request, $success->getRequest());
        $this->assertEquals('CGI Program Runner', $success->getCheckName());
    }
}
