<?php

namespace PhpSchool\PhpWorkshop\Result\Cgi;

use Psr\Http\Message\RequestInterface;

/**
 * Base CGI result interface
 */
interface ResultInterface extends \PhpSchool\PhpWorkshop\Result\ResultInterface
{
    /**
     * Get the request associated with this result.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface;
}
