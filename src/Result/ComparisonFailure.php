<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
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
    public function __construct($name, $expectedValue, $actualValue)
    {
        $this->name          = $name;
        $this->expectedValue = $expectedValue;
        $this->actualValue   = $actualValue;
    }

    /**
     * @param string $name
     * @param string $expectedValue
     * @param string $actualValue
     * @return static
     */
    public static function fromNameAndValues($name, $expectedValue, $actualValue)
    {
        return new static($name, $expectedValue, $actualValue);
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }

    /**
     * Get the expected value.
     *
     * @return string
     */
    public function getExpectedValue()
    {
        return $this->expectedValue;
    }

    /**
     * Get the actual value.
     *
     * @return string
     */
    public function getActualValue()
    {
        return $this->actualValue;
    }
}
