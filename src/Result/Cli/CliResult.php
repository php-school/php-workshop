<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use ArrayIterator;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\ResultGroupInterface;

/**
 * A result which encompasses all the results for each individual execution made during
 * the CLI verification process.
 *
 * @implements IteratorAggregate<int, ResultInterface>
 */
class CliResult implements ResultGroupInterface, IteratorAggregate
{
    /**
     * @var string
     */
    private $name = 'CLI Program Runner';

    /**
     * @var ResultInterface[]
     */
    private $results = [];

    /**
     * @param array<ResultInterface> $requestResults An array of results representing each request.
     */
    public function __construct(array $requestResults = [])
    {
        foreach ($requestResults as $request) {
            $this->add($request);
        }
    }

    /**
     * Add a new result.
     *
     * @param ResultInterface $result
     */
    public function add(ResultInterface $result): void
    {
        $this->results[] = $result;
    }

    /**
     * Get the name of the check that this result was produced from, most likely the CGI Runner.
     *
     * @return string
     */
    public function getCheckName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return count(
            array_filter($this->results, function ($result) {
                return $result instanceof FailureInterface;
            })
        ) === 0;
    }

    /**
     * @return array<ResultInterface>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Get an iterator in order to `foreach` the results.
     *
     * @return ArrayIterator<int, ResultInterface>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->results);
    }
}
