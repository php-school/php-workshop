<?php

namespace PhpSchool\PhpWorkshop\Exercise\Scenario;

use Psr\Http\Message\RequestInterface;

class CgiScenario extends ExerciseScenario
{
    /**
     * @var array<RequestInterface>
     */
    private array $executions = [];

    public function withExecution(RequestInterface $request): self
    {
        $this->executions[] = $request;

        return $this;
    }

    /**
     * @return array<RequestInterface>
     */
    public function getExecutions(): array
    {
        return $this->executions;
    }
}
