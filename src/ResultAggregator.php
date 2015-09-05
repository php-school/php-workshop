<?php

namespace PhpWorkshop\PhpWorkshop;

/**
 * Class ResultAggregator
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultAggregator
{
    /**
     * @var Fail[]|Success[]
     */
    private $results = [];

    /**
     * @param Fail|Success $result
     */
    public function add($result)
    {
        if (!$result instanceof Fail && !$result instanceof Success) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected instance of "%s" or "%s". Received: "%s"',
                    Success::class,
                    Fail::class,
                    is_object($result) ? get_class($result) : gettype($result)
                )
            );
        }

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
            function (Fail $fail) {
                return $fail->getReason();
            },
            array_filter($this->results, function ($result) {
                return $result instanceof Fail;
            })
        );
    }
}