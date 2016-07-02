<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * A failure result representing the situation where there were function usage requirements
 * and they were not met.
 *
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
     * @param CheckInterface $check The check that produced this result.
     * @param array $bannedFunctions A list of functions that were used, but were banned.
     * @param array $missingFunctions A list of functions that were not used, but were required.
     */
    public function __construct(CheckInterface $check, array $bannedFunctions, array $missingFunctions)
    {
        $this->check            = $check;
        $this->bannedFunctions  = $bannedFunctions;
        $this->missingFunctions = $missingFunctions;
    }

    /**
     * Get the list of functions that were used, but were banned.
     *
     * @return array
     */
    public function getBannedFunctions()
    {
        return $this->bannedFunctions;
    }

    /**
     * Get the list of functions that were not used, but were required.
     *
     * @return array
     */
    public function getMissingFunctions()
    {
        return $this->missingFunctions;
    }
}
