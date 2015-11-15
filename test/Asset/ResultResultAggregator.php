<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class ResultResultAggregator
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ResultResultAggregator extends ResultAggregator implements ResultInterface
{

    /**
     * @return string
     */
    public function getCheckName()
    {
        return self::class;
    }
}
