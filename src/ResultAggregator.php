<?php

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * This class is a container to hold all the results produced
 * throughout the verification of a students solution.
 *
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
    public function add(ResultInterface $result)
    {
        $this->results[] = $result;
    }

    /**
     * Computed whether the results are considered a success. If there are any results which implement
     * `FailureInterface` then the combined result is considered as a fail.
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return count(
            array_filter($this->results, function ($result) {
                if ($result instanceof self) {
                    return !$result->isSuccessful();
                }
                return $result instanceof FailureInterface;
            })
        ) === 0;
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
