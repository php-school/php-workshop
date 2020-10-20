<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use ArrayIterator;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\ResultGroupInterface;

/**
 * A result which encompasses all the results for each individual execution made during
 * the CLI verification process.
 */
class CliResult implements ResultGroupInterface, IteratorAggregate
{
    /**
     * @var string
     */
    private $name = 'CLI Program Runner';

    /**
     * @var array
     */
    private $results = [];

    /**
     * @param array $requestResults An array of results representing each request.
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
    public function add(ResultInterface $result)
    {
        $this->results[] = $result;
    }

    /**
     * Get the name of the check that this result was produced from, most likely the CGI Runner.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return count(
            array_filter($this->results, function ($result) {
                return $result instanceof FailureInterface;
            })
        ) === 0;
    }

    /**
     * @return ResultInterface
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Get an iterator in order to `foreach` the results.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}
