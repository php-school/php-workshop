<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class CgiOutFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResult extends ResultAggregator implements ResultInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     * @param array $requestResults
     */
    public function __construct($name, array $requestResults)
    {
        $this->name = $name;
        foreach ($requestResults as $request) {
            $this->add($request);
        }
    }


    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
