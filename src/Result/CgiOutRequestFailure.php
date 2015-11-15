<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class CgiOutRequestFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutRequestFailure implements FailureInterface
{
    use ResultTrait;

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
     * @param CheckInterface $check
     * @param RequestInterface $request
     * @param string $expectedOutput
     * @param string $actualOutput
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    public function __construct(
        CheckInterface $check,
        RequestInterface $request,
        $expectedOutput,
        $actualOutput,
        array $expectedHeaders,
        array $actualHeaders
    ) {
        $this->check            = $check;
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
}
