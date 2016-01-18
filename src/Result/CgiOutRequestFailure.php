<?php

namespace PhpSchool\PhpWorkshop\Result;

use Psr\Http\Message\RequestInterface;

/**
 * Class CgiOutRequestFailure
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
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return bool
     */
    public function bodyDifferent()
    {
        return $this->expectedOutput !== $this->actualOutput;
    }

    /**
     * @return bool
     */
    public function headersDifferent()
    {
        return $this->expectedHeaders !== $this->actualHeaders;
    }

    /**
     * @return bool
     */
    public function headersAndBodyDifferent()
    {
        return $this->bodyDifferent() && $this->headersDifferent();
    }

    /**
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * @return string
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * @return array
     */
    public function getExpectedHeaders()
    {
        return $this->expectedHeaders;
    }

    /**
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
