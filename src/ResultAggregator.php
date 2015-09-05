<?php

namespace PhpWorkshop\PhpWorkshop;

use PhpWorkshop\PhpWorkshop\Result\Failure;
use PhpWorkshop\PhpWorkshop\Result\ResultInterface;
use PhpWorkshop\PhpWorkshop\Result\Success;

/**
 * Class ResultAggregator
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultAggregator
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
                return $result instanceof Success;
            })
        ) > 0;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return array_map(
            function (Failure $failure) {
                return $failure->getReason();
            },
            array_filter($this->results, function ($result) {
                return $result instanceof Failure;
            })
        );
    }
}