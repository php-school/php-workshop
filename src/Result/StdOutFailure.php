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
     * @param string $expectedOutput
     * @param string $actualOutput
     */
    public function __construct($expectedOutput, $actualOutput)
    {
        $this->expectedOutput = $expectedOutput;
        $this->actualOutput = $actualOutput;
        $reason = sprintf('Output did not match. Expected: "%s". Received: "%s"', $expectedOutput, $actualOutput);
        parent::__construct('Program Output', $reason);
    }

    /**
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * @return mixed
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }
}
