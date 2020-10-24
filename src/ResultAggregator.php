<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultGroupInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * This class is a container to hold all the results produced
 * throughout the verification of a students solution.
 *
 * @implements IteratorAggregate<int, ResultInterface>
 */
class ResultAggregator implements IteratorAggregate
{
    /**
     * @var ResultInterface[]
     */
    private $results = [];

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
     * Computes whether the results are considered a success. If there are any results which implement
     * `FailureInterface` then the combined result is considered as a fail.
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return count(
            array_filter($this->results, function ($result) {
                if ($result instanceof ResultGroupInterface) {
                    return !$result->isSuccessful();
                }
                return $result instanceof FailureInterface;
            })
        ) === 0;
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
