<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class StdOutFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailure implements FailureInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;

    /**
     * @var string
     */
    private $warnings;

    /**
     * @param string $name
     * @param string $expectedOutput
     * @param string $actualOutput
     */
    public function __construct($name, $expectedOutput, $actualOutput, $warnings = null)
    {
        $this->name             = $name;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
        $this->warnings         = $warnings;
    }

    /**
     * @param string $name
     * @param $expectedOutput
     * @param $actualOutput
     * @return static
     */
    public static function fromNameAndOutput($name, $expectedOutput, $actualOutput)
    {
        return new static($name, $expectedOutput, $actualOutput);
    }

    /**
     * @param string $name
     * @param $expectedOutput
     * @param $actualOutput
     * @param $warnings
     * @return static
     */
    public static function fromNameAndWarnings($name, $expectedOutput, $actualOutput, $warnings)
    {
        return new static($name, $expectedOutput, $actualOutput, $warnings);
    }

    /**
     * @param CheckInterface $check
     * @param $expectedOutput
     * @param $actualOutput
     * @return static
     */
    public static function fromCheckAndOutput(CheckInterface $check, $expectedOutput, $actualOutput)
    {
        return new static($check->getName(), $expectedOutput, $actualOutput);
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
    
    /**
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * @return string
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * @return string
     */
    public function getWarnings()
    {
        return $this->warnings;
    }
}
