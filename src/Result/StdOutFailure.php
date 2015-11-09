<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class StdOutFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class StdOutFailure implements ResultInterface
{
    use ResultTrait;

    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;

    /**
     * @param CheckInterface $check
     * @param string $expectedOutput
     * @param string $actualOutput
     */
    public function __construct(CheckInterface $check, $expectedOutput, $actualOutput)
    {
        $this->check            = $check;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
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
