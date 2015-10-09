<?php

namespace PhpWorkshop\PhpWorkshop\Result;

/**
 * Class StdOutFailure
 * @package PhpWorkshop\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailure extends Failure
{
    /**
     * @var
     */
    private $expectedOutput;
    /**
     * @var
     */
    private $actualOutput;

    /**
     * @param string $reason
     * @param        $expectedOutput
     * @param        $actualOutput
     */
    public function __construct($reason, $expectedOutput, $actualOutput)
    {
        $this->expectedOutput = $expectedOutput;
        $this->actualOutput = $actualOutput;
        parent::__construct('Program Output', $reason);
    }

    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    public function getActualOutput()
    {
        return $this->actualOutput;
    }
}