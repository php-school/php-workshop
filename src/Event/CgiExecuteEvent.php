<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exercise\ExerciseInterface;
use PhpSchool\PhpWorkshop\Exercise\Scenario\CgiScenario;
use PhpSchool\PhpWorkshop\ExerciseRunner\Context\ExecutionContext;
use PhpSchool\PhpWorkshop\Input\Input;
use Psr\Http\Message\RequestInterface;

/**
 * An event to represent events which occur throughout the verification and running process in
 * `\PhpSchool\PhpWorkshop\ExerciseRunner\CgiRunner`.
 */
class CgiExecuteEvent extends CgiExerciseRunnerEvent
{
    private RequestInterface $request;

    /**
     * @param string $name The event name.
     * @param RequestInterface $request The request that will be performed.
     * @param array<string, mixed> $parameters The event parameters.
     */
    public function __construct(
        string $name,
        ExecutionContext $context,
        CgiScenario $scenario,
        RequestInterface $request,
        array $parameters = []
    ) {
        $parameters['request'] = $request;
        parent::__construct($name, $context, $scenario, $parameters);
        $this->request = $request;
    }

    /**
     * Add a header to the request.
     *
     * @param string $header
     * @param string|array<string> $value
     */
    public function addHeaderToRequest(string $header, $value): void
    {
        $this->request = $this->request->withHeader($header, $value);
    }

    /**
     * Modify the request via a callback. The callback should return the newly modified request.
     *
     * @param callable $callback
     */
    public function modifyRequest(callable $callback): void
    {
        $this->request = $callback($this->request);
    }

    /**
     * Get the request.
     *
     * @return RequestInterface
     */
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
