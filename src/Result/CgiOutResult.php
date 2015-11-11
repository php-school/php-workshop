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
    use ResultTrait;

    /**
     * CgiOutResult constructor.
     * @param CheckInterface $check
     * @param array $requestResults
     */
    public function __construct(CheckInterface $check, array $requestResults)
    {
        $this->check = $check;
        foreach ($requestResults as $request) {
            $this->add($request);
        }
    }
}
