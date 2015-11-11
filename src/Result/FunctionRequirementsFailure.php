<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class FunctionRequirementsFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailure implements FailureInterface
{
    use ResultTrait;
    
    /**
     * @var array
     */
    private $bannedFunctions;

    /**
     * @var array
     */
    private $missingFunctions;

    /**
     * @param CheckInterface $check
     * @param array $bannedFunctions
     * @param array $missingFunctions
     */
    public function __construct(CheckInterface $check, array $bannedFunctions, array $missingFunctions)
    {
        $this->check            = $check;
        $this->bannedFunctions  = $bannedFunctions;
        $this->missingFunctions = $missingFunctions;
    }

    /**
     * @return array
     */
    public function getBannedFunctions()
    {
        return $this->bannedFunctions;
    }

    /**
     * @return array
     */
    public function getMissingFunctions()
    {
        return $this->missingFunctions;
    }
}
