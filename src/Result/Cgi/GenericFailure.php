<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result\Cgi;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Failure;
use Psr\Http\Message\RequestInterface;

/**
 * Generic CGI failure.
 */
class GenericFailure extends Failure implements FailureInterface
{

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private static $name = 'CGI Program Runner';

    /**
     * @param RequestInterface $request The request that caused the failure.
     * @param string|null $reason
     */
    public function __construct(RequestInterface $request, string $reason = null)
    {
        $this->request = $request;
        parent::__construct(static::$name, $reason);
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param RequestInterface $request The request that caused the failure.
     * @param string|null $reason The reason (if any) of the failure.
     * @return self The result.
     */
    public static function fromRequestAndReason(RequestInterface $request, string $reason = null): self
    {
        return new self($request, $reason);
    }

    /**
     * Static constructor to create from a `PhpSchool\PhpWorkshop\Exception\CodeExecutionException` exception.
     *
     * @param RequestInterface $request The request that caused the failure.
     * @param CodeExecutionException $e The exception.
     * @return self The result.
     */
    public static function fromRequestAndCodeExecutionFailure(
        RequestInterface $request,
        CodeExecutionException $e
    ): self {
        return new self($request, $e->getMessage());
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
}
