<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * Class FunctionRequirementsFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class FunctionRequirementsFailure extends Failure
{
    /**
     * @var array
     */
    private $bannedFunctions;

    /**
     * @var array
     */
    private $missingFunctions;

    /**
     * @param array $bannedFunctions
     * @param array $missingFunctions
     */
    public function __construct(array $bannedFunctions, array $missingFunctions)
    {
        $reason = 'Function Requirements were not met';
        parent::__construct('Function Requirements', $reason);
        $this->bannedFunctions = $bannedFunctions;
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
