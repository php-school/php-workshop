<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Result to represent a failed file comparison
 */
class FileComparisonFailure implements FailureInterface
{
    use ResultTrait;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $expectedValue;

    /**
     * @var string
     */
    private $actualValue;

    /**
     * @param CheckInterface $check The check that produced this result.
     * @param string $fileName
     * @param string $expectedValue
     * @param string $actualValue
     */
    public function __construct(CheckInterface $check, string $fileName, string $expectedValue, string $actualValue)
    {
        $this->check = $check;
        $this->fileName = $fileName;
        $this->expectedValue = $expectedValue;
        $this->actualValue = $actualValue;
    }

    /**
     * Get the name of the file to be verified
     *
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Get the expected value.
     *
     * @return string
     */
    public function getExpectedValue(): string
    {
        return $this->expectedValue;
    }

    /**
     * Get the actual value.
     *
     * @return string
     */
    public function getActualValue(): string
    {
        return $this->actualValue;
    }

    /**
     * @return array{file_name: string, expected_value: string, actual_value: string}
     */
    public function toArray(): array
    {
        return [
            'file_name' => $this->getFileName(),
            'expected_value' => $this->getExpectedValue(),
            'actual_value' => $this->getActualValue()
        ];
    }
}
