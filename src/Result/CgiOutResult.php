<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\ResultAggregator;

/**
 * Class CgiOutFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutResult extends ResultAggregator implements ResultInterface
{
    use ResultTrait;
}
