<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * A failure result representing the situation where the output of a solution does not match
 * that of the expected output in the context of a CLI request.
 */
class RequestFailure implements FailureInterface
{
    /**
     * @var ArrayObject<int, string>
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
     * @param ArrayObject<int, string> $args The arguments that caused the failure.
     * @param string $expectedOutput The expected output.
     * @param string $actualOutput The actual output.
     */
    public function __construct(ArrayObject $args, string $expectedOutput, string $actualOutput)
    {
        $this->args = $args;
        $this->expectedOutput = $expectedOutput;
        $this->actualOutput = $actualOutput;
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param ArrayObject<int, string> $args The arguments that caused the failure.
     * @param string $expectedOutput The expected result.
     * @param string $actualOutput The actual output.
     * @return self The result.
     */
    public static function fromArgsAndOutput(ArrayObject $args, string $expectedOutput, string $actualOutput): self
    {
        return new self($args, $expectedOutput, $actualOutput);
    }

    /**
     * Get the arguments that caused the failure.
     *
     * @return ArrayObject<int, string>
     */
    public function getArgs(): ArrayObject
    {
        return $this->args;
    }

    /**
     * Get the expected output.
     *
     * @return string
     */
    public function getExpectedOutput(): string
    {
        return $this->expectedOutput;
    }

    /**
     * Get the actual output.
     *
     * @return string
     */
    public function getActualOutput(): string
    {
        return $this->actualOutput;
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName(): string
    {
        return 'Request Failure';
    }

    /**
     * @return array{
     *    args: array<string>,
     *    expected_output: string,
     *    actual_output: string,
     * }
     */
    public function toArray(): array
    {
        return [
            'args' => $this->getArgs()->getArrayCopy(),
            'expected_output' => $this->getExpectedOutput(),
            'actual_output' => $this->getActualOutput()
        ];
    }
}
