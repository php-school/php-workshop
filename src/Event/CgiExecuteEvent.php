<?php

namespace PhpSchool\PhpWorkshop\Event;

use Assert\Assertion;
use Psr\Http\Message\RequestInterface;

/**
 * An event to represent events which occur throughout the verification and running process in
 * `\PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner`
 *
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
     * @param string $name The event name.
     * @param RequestInterface $request The request that will be performed.
     * @param array $parameters The event parameters.
     */
    public function __construct($name, RequestInterface $request, array $parameters = [])
    {
        $parameters['request'] = $request;
        parent::__construct($name, $parameters);
        $this->request = $request;
    }

    /**
     * Add a header to the request.
     *
     * @param string $header
     * @param string|string[] $value
     */
    public function addHeaderToRequest($header, $value)
    {
        $this->request = $this->request->withHeader($header, $value);
    }

    /**
     * Modify the request via a callback. The callback should return the new modified request.
     *
     * @param callable $callback
     */
    public function modifyRequest(callable $callback)
    {
        $this->request = $callback($this->request);
    }

    /**
     * Get the request.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
