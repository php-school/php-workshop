<?php

namespace PhpSchool\PhpWorkshop\Result\Cgi;

use Psr\Http\Message\RequestInterface;

/**
 * A failure result representing the situation where the output of a student's solution did not match the
 * expected output in the context of a HTTP request.
 */
class RequestFailure implements FailureInterface
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
     * @var array<string, string>
     */
    private $expectedHeaders;
    
    /**
     * @var array<string, string>
     */
    private $actualHeaders;

    /**
     * @param RequestInterface $request The request that caused the failure.
     * @param string $expectedOutput
     * @param string $actualOutput
     * @param array<string, string> $expectedHeaders
     * @param array<string, string> $actualHeaders
     */
    public function __construct(
        RequestInterface $request,
        string $expectedOutput,
        string $actualOutput,
        array $expectedHeaders,
        array $actualHeaders
    ) {
        $this->request = $request;
        $this->expectedOutput = $expectedOutput;
        $this->actualOutput = $actualOutput;
        $this->expectedHeaders = $expectedHeaders;
        $this->actualHeaders = $actualHeaders;
    }

    /**
     * Get the request that caused the failure.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    /**
     * Is the output different?
     *
     * @return bool
     */
    public function bodyDifferent(): bool
    {
        return $this->expectedOutput !== $this->actualOutput;
    }

    /**
     * Are the headers different?
     *
     * @return bool
     */
    public function headersDifferent(): bool
    {
        return $this->expectedHeaders !== $this->actualHeaders;
    }

    /**
     * Are the headers & body different?
     *
     * @return bool
     */
    public function headersAndBodyDifferent(): bool
    {
        return $this->bodyDifferent() && $this->headersDifferent();
    }

    /**
     * Get the expected output.
     *
     * @return string
     */
    public function getExpectedOutput(): string
    {
        return $this->expectedOutput;
    }

    /**
     * Get the actual output.
     *
     * @return string
     */
    public function getActualOutput(): string
    {
        return $this->actualOutput;
    }

    /**
     * Get the array of expected headers.
     *
     * @return array<string, string>
     */
    public function getExpectedHeaders(): array
    {
        return $this->expectedHeaders;
    }

    /**
     * Get the array of actual headers.
     *
     * @return array<string, string>
     */
    public function getActualHeaders(): array
    {
        return $this->actualHeaders;
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName(): string
    {
        return 'Request Failure';
    }
}
