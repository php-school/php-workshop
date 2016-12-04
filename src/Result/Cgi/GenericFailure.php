<?php

namespace PhpSchool\PhpWorkshop\Result\Cgi;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Failure;
use Psr\Http\Message\RequestInterface;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
     * @param null $reason
     */
    public function __construct(RequestInterface $request, $reason = null)
    {
        $this->request = $request;
        parent::__construct(static::$name, $reason);
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param RequestInterface $request The request that caused the failure.
     * @param string|null $reason The reason (if any) of the failure.
     * @return static The result.
     */
    public static function fromRequestAndReason(RequestInterface $request, $reason)
    {
        return new static($request, $reason);
    }

    /**
     * Static constructor to create from a `PhpSchool\PhpWorkshop\Exception\CodeExecutionException` exception.
     *
     * @param RequestInterface $request The request that caused the failure.
     * @param CodeExecutionException $e The exception.
     * @return static The result.
     */
    public static function fromRequestAndCodeExecutionFailure(RequestInterface $request, CodeExecutionException $e)
    {
        return new static($request, $e->getMessage());
    }

    /**
     * Get the request that caused the failure.
     *
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
