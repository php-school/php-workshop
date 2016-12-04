<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * A failure result representing the situation where the output of a solution does not match
 * that of the expected output in the context of a CLI request.
 *
 * @package PhpSchool\PhpWorkshop\Result
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class RequestFailure implements FailureInterface
{
    /**
     * @var ArrayObject
     */
    private $args;

    /**
     * @var string
     */
    private $expectedOutput;

    /**
     * @var string
     */
    private $actualOutput;

    /**
     * @param ArrayObject $args
     * @param string $expectedOutput The expected output.
     * @param string $actualOutput The actual output.
     */
    public function __construct(ArrayObject $args, $expectedOutput, $actualOutput)
    {
        $this->args             = $args;
        $this->expectedOutput   = $expectedOutput;
        $this->actualOutput     = $actualOutput;
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param ArrayObject $args
     * @param string $expectedOutput The expected result.
     * @param string $actualOutput The actual output.
     * @return static The result.
     */
    public static function fromArgsAndOutput(ArrayObject $args, $expectedOutput, $actualOutput)
    {
        return new static($args, $expectedOutput, $actualOutput);
    }

    /**
     * @return ArrayObject
     */
    public function getArgs()
    {
        return $this->args;
    }
    
    /**
     * Get the expected output.
     *
     * @return string
     */
    public function getExpectedOutput()
    {
        return $this->expectedOutput;
    }

    /**
     * Get the actual output.
     *
     * @return string
     */
    public function getActualOutput()
    {
        return $this->actualOutput;
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return 'Request Failure';
    }
}
