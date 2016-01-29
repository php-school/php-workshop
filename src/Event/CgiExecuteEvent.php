<?php

namespace PhpSchool\PhpWorkshop\Event;

use Assert\Assertion;
use Psr\Http\Message\RequestInterface;

/**
 * Class CgiExecuteEvent
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiExecuteEvent extends Event
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param string $name
     * @param RequestInterface $request
     * @param array $parameters
     */
    public function __construct($name, RequestInterface $request, array $parameters = [])
    {
        $parameters['request'] = $request;
        parent::__construct($name, $parameters);
        $this->request = $request;
    }

    /**
     * @param string $header
     * @param string|string[] $value
     */
    public function addHeaderToRequest($header, $value)
    {
        $this->request = $this->request->withHeader($header, $value);
    }

    /**
     * @param callable $callback
     */
    public function modifyRequest(callable $callback)
    {
        $this->request = $callback($this->request);
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
