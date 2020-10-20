<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Result\ResultInterface;
use PhpSchool\PhpWorkshop\ResultAggregator;

class ResultResultAggregator extends ResultAggregator implements ResultInterface
{
    public function getCheckName(): string
    {
        return self::class;
    }
}
