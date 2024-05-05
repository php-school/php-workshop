<?php

namespace PhpSchool\PhpWorkshop\Environment;

use PhpSchool\PhpWorkshop\Utils\Collection;
use Psr\Http\Message\RequestInterface;

class CgiTestEnvironment extends TestEnvironment
{
    /**
     * @var array<RequestInterface>
     */
    public array $executions = [];

    /**
     * @var array<string, string>
     */
    public array $files = [];

    public function withExecution(RequestInterface $request): self
    {
        $this->executions[] = $request;

        return $this;
    }
}
