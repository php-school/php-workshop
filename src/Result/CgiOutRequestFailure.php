<?php

namespace PhpSchool\PhpWorkshop\Result;

use Psr\Http\Message\RequestInterface;

/**
 * A failure result representing the situation where the output of a student's solution did not match the
 * expected output in the context of a HTTP request.
 *
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutRequestFailure implements FailureInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;
    
    /**
     * @var array
     */
    private $expectedHeaders;
    
    /**
     * @var array
     */
    private $actualHeaders;

    /**
     * @param RequestInterface $request
     * @param string $expectedOutput
     * @param string $actualOutput
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    public function __construct(
        RequestInterface $request,
        $expectedOutput,
        $actualOutput,
        array $expectedHeaders,
        array $actualHeaders
    ) {
        $this->request          = $request;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
        $this->expectedHeaders  = $expectedHeaders;
        $this->actualHeaders    = $actualHeaders;
    }

    /**
     * Get the request object associated with this failure.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Is the output different?
     *
     * @return bool
     */
    public function bodyDifferent()
    {
        return $this->expectedOutput !== $this->actualOutput;
    }

    /**
     * Are the headers different?
     *
     * @return bool
     */
    public function headersDifferent()
    {
        return $this->expectedHeaders !== $this->actualHeaders;
    }

    /**
     * Are the headers & body different?
     *
     * @return bool
     */
    public function headersAndBodyDifferent()
    {
        return $this->bodyDifferent() && $this->headersDifferent();
    }

    /**
     * Get the expected output.
     *
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * Get the actual output.
     *
     * @return string
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * Get the array of expected headers.
     *
     * @return array
     */
    public function getExpectedHeaders()
    {
        return $this->expectedHeaders;
    }

    /**
     * Get the array of actual headers.
     *
     * @return array
     */
    public function getActualHeaders()
    {
        return $this->actualHeaders;
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return 'Request Failure';
    }
}
