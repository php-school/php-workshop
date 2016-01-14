<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PHPUnit_Framework_TestCase;
use Psr\Http\Message\RequestInterface;
use Zend\Diactoros\Request;

/**
 * Class CgiExecuteEventTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiExecuteEventTest extends PHPUnit_Framework_TestCase
{
    public function testAddHeader()
    {
        $request = new Request;
        $e = new CgiExecuteEvent('event', $request);

        $e->addHeaderToRequest('Content-Type', 'text/html');
        $this->assertSame(['Content-Type' => ['text/html']], $e->getRequest()->getHeaders());
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testModifyRequest()
    {
        $request = new Request;
        $e = new CgiExecuteEvent('event', $request);

        $e->modifyRequest(function (RequestInterface $request) {
            return $request
                ->withHeader('Content-Type', 'text/html')
                ->withMethod('POST');
        });
        $this->assertSame(['Content-Type' => ['text/html']], $e->getRequest()->getHeaders());
        $this->assertSame('POST', $e->getRequest()->getMethod());
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testGetRequest()
    {
        $request = new Request;
        $e = new CgiExecuteEvent('event', $request);

        $this->assertSame($request, $e->getRequest());
    }
}
