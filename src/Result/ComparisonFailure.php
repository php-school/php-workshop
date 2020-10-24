<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

/**
 * Result to represent a failed comparison
 */
class ComparisonFailure implements FailureInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expectedValue;

    /**
     * @var string
     */
    private $actualValue;

    /**
     * @param string $name
     * @param string $expectedValue
     * @param string $actualValue
     */
    public function __construct(string $name, string $expectedValue, string $actualValue)
    {
        $this->name = $name;
        $this->expectedValue = $expectedValue;
        $this->actualValue = $actualValue;
    }

    /**
     * @param string $name
     * @param string $expectedValue
     * @param string $actualValue
     * @return self
     */
    public static function fromNameAndValues(string $name, string $expectedValue, string $actualValue): self
    {
        return new self($name, $expectedValue, $actualValue);
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName(): string
    {
        return $this->name;
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
}
