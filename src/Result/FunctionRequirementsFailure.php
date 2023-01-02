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
     * @var array<array{function: string, line: int}>
     */
    private $bannedFunctions;

    /**
     * @var array<int, string>
     */
    private $missingFunctions;

    /**
     * @param CheckInterface $check The check that produced this result.
     * @param array<array{function: string, line: int}> $bannedFunctions Functions that were used, but were banned.
     * @param array<int, string> $missingFunctions Functions that were not used, but were required.
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
     * @return array<array{function: string, line: int}>
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

    public function toArray(): array
    {
        return [
            'banned_functions' => $this->getBannedFunctions(),
            'missing_functions' => $this->getMissingFunctions()
        ];
    }
}
