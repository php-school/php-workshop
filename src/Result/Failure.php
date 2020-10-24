<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpParser\Error as ParseErrorException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;

/**
 * Default implementation of `PhpSchool\PhpWorkshop\Result\FailureInterface`.
 */
class Failure implements FailureInterface
{
    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string
     */
    private $name;

    /**
     * Create an instance from the name of the check that produces this result
     * and the reason for the failure.
     *
     * @param string $name The name of the check that produced this result.
     * @param string|null $reason The reason (if any) of the failure.
     */
    public function __construct(string $name, string $reason = null)
    {
        $this->name = $name;
        $this->reason = $reason;
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param string $name The name of the check that produced this result.
     * @param string|null $reason The reason (if any) of the failure.
     * @return self The result.
     */
    public static function fromNameAndReason(string $name, string $reason = null): self
    {
        return new self($name, $reason);
    }

    /**
     * Static constructor to create from an instance of `PhpSchool\PhpWorkshop\Check\CheckInterface`.
     *
     * @param CheckInterface $check The check instance.
     * @param string|null $reason The reason (if any) of the failure.
     * @return self The result.
     */
    public static function fromCheckAndReason(CheckInterface $check, string $reason = null): self
    {
        return new self($check->getName(), $reason);
    }

    /**
     * Static constructor to create from a `PhpSchool\PhpWorkshop\Exception\CodeExecutionException` exception.
     *
     * @param string $name The name of the check that produced this result.
     * @param CodeExecutionException $e The exception.
     * @return self The result.
     */
    public static function fromNameAndCodeExecutionFailure(string $name, CodeExecutionException $e): self
    {
        return new self($name, $e->getMessage());
    }

    /**
     * Static constructor to create from a `PhpParser\Error` exception. Many checks will need to parse the student's
     * solution, so this serves as a helper to create a consistent failure.
     *
     * @param CheckInterface $check The check that attempted to parse the solution.
     * @param ParseErrorException $e The parse exception.
     * @param string $file The absolute path to the solution.
     * @return self The result.
     */
    public static function fromCheckAndCodeParseFailure(
        CheckInterface $check,
        ParseErrorException $e,
        string $file
    ): self {
        return new self(
            $check->getName(),
            sprintf('File: "%s" could not be parsed. Error: "%s"', $file, $e->getMessage())
        );
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
     * Get the reason, or `null` if there is no reason.
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}
