<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use GuzzleHttp\Psr7\Request;
use PhpSchool\PhpWorkshop\Event\CgiExecuteEvent;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CgiContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\CliContext;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use PhpSchool\PhpWorkshopTest\Asset\CgiExerciseImpl;
use PhpSchool\PhpWorkshopTest\Asset\CliExerciseImpl;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class CgiExecuteEventTest extends TestCase
{
    public function testAddHeader(): void
    {
        $context = new CgiContext(new ExecutionContext('', '', new CgiExerciseImpl(), new Input('test', [])));

        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $context, $request);

        $e->addHeaderToRequest('Content-Type', 'text/html');
        $this->assertSame(
            [
                'Host' => ['some.site'],
                'Content-Type' => ['text/html'],
            ],
            $e->getRequest()->getHeaders()
        );
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testModifyRequest(): void
    {
        $context = new CgiContext(new ExecutionContext('', '', new CgiExerciseImpl(), new Input('test', [])));

        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $context, $request);

        $e->modifyRequest(function (RequestInterface $request) {
            return $request
                ->withHeader('Content-Type', 'text/html')
                ->withMethod('POST');
        });
        $this->assertSame(
            [
                'Host' => ['some.site'],
                'Content-Type' => ['text/html'],
            ],
            $e->getRequest()->getHeaders()
        );
        $this->assertSame('POST', $e->getRequest()->getMethod());
        $this->assertNotSame($request, $e->getRequest());
    }

    public function testGetRequest(): void
    {
        $context = new CgiContext(new ExecutionContext('', '', new CgiExerciseImpl(), new Input('test', [])));

        $request = new Request('GET', 'https://some.site');
        $e = new CgiExecuteEvent('event', $context, $request);

        $this->assertSame($request, $e->getRequest());
    }
}
