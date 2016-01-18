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
     * @param string $name
     * @param string $expectedOutput
     * @param string $actualOutput
     */
    public function __construct($name, $expectedOutput, $actualOutput)
    {
        $this->name             = $name;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
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
}
