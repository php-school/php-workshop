<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class CgiOutBodyFailure
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CgiOutFailure implements ResultInterface
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
     * @var array
     */
    private $expectedHeaders;
    
    /**
     * @var array
     */
    private $actualHeaders;

    /**
     * @param CheckInterface $check
     * @param string $expectedOutput
     * @param string $actualOutput
     * @param array $expectedHeaders
     * @param array $actualHeaders
     */
    public function __construct(
        CheckInterface $check,
        $expectedOutput,
        $actualOutput,
        array $expectedHeaders,
        array $actualHeaders
    ) {
        $this->check            = $check;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
        $this->expectedHeaders  = $expectedHeaders;
        $this->actualHeaders    = $actualHeaders;
    }

    /**
     * @return bool
     */
    public function bodyDifferent()
    {
        return $this->expectedOutput !== $this->actualOutput;
    }

    /**
     * @return bool
     */
    public function headersDifferent()
    {
        return $this->expectedHeaders !== $this->actualHeaders;
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
     * @return array
     */
    public function getExpectedHeaders()
    {
        return $this->expectedHeaders;
    }

    /**
     * @return array
     */
    public function getActualHeaders()
    {
        return $this->actualHeaders;
    }
}
