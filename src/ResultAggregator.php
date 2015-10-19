<?php

namespace PhpSchool\PhpWorkshop;

use ArrayIterator;
use IteratorAggregate;
use PhpSchool\PhpWorkshop\Result\Failure;
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
                return $result instanceof Failure;
            })
        ) === 0;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return array_values(array_map(
            function (Failure $failure) {
                return $failure->getReason();
            },
            array_filter($this->results, function ($result) {
                return $result instanceof Failure;
            })
        ));
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->results);
    }
}
