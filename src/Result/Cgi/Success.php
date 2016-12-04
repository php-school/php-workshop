<?php

namespace PhpSchool\PhpWorkshop\Result\Cgi;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Default implementation of `PhpSchool\PhpWorkshop\Result\SuccessInterface`.
 *
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success implements SuccessInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $name = 'CGI Program Runner';

    /**
     * @param RequestInterface $request The request that caused the failure.
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
