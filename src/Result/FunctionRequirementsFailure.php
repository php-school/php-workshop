<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * A failure result representing the situation where there were function usage requirements
 * and they were not met.
 */
class FunctionRequirementsFailure implements FailureInterface
{
    use ResultTrait;

    /**
     * @var array<int, string>
     */
    private $bannedFunctions;

    /**
     * @var array<int, string>
     */
    private $missingFunctions;

    /**
     * @param CheckInterface $check The check that produced this result.
     * @param array<int, string> $bannedFunctions A list of functions that were used, but were banned.
     * @param array<int, string> $missingFunctions A list of functions that were not used, but were required.
     */
    public function __construct(CheckInterface $check, array $bannedFunctions, array $missingFunctions)
    {
        $this->check = $check;
        $this->bannedFunctions = $bannedFunctions;
        $this->missingFunctions = $missingFunctions;
    }

    /**
     * Get the list of functions that were used, but were banned.
     *
     * @return array<int, string>
     */
    public function getBannedFunctions(): array
    {
        return $this->bannedFunctions;
    }

    /**
     * Get the list of functions that were not used, but were required.
     *
     * @return array<int, string>
     */
    public function getMissingFunctions(): array
    {
        return $this->missingFunctions;
    }
}
