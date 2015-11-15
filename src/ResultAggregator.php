<?php

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\FailureInterface;
use PhpSchool\PhpWorkshop\Result\ResultInterface;

/**
 * Class ResultAggregator
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
     * @param ResultInterface $result
     */
    public function add(ResultInterface $result)
    {
        $this->results[] = $result;
    }

    /**
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
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}
