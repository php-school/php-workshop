<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * Generic CLI failure.
 */
class GenericFailure extends Failure implements FailureInterface
{
    /**
     * @var ArrayObject<string>
     */
    private $args;

    /**
     * @var string
     */
    private static $name = 'CLI Program Runner';

    /**
     * @param ArrayObject<string> $args The arguments that caused the failure.
     * @param string|null $reason The reason (if any) of the failure.
     */
    public function __construct(ArrayObject $args, $reason = null)
    {
        $this->args = $args;
        parent::__construct(static::$name, $reason);
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param ArrayObject<string> $args The arguments that caused the failure.
     * @param string|null $reason The reason (if any) of the failure.
     * @return self The result.
     */
    public static function fromArgsAndReason(ArrayObject $args, ?string $reason): self
    {
        return new self($args, $reason);
    }

    /**
     * Static constructor to create from a `PhpSchool\PhpWorkshop\Exception\CodeExecutionException` exception.
     *
     * @param ArrayObject<string> $args The arguments that caused the failure.
     * @param CodeExecutionException $e The exception.
     * @return self The result.
     */
    public static function fromArgsAndCodeExecutionFailure(ArrayObject $args, CodeExecutionException $e): self
    {
        return new self($args, $e->getMessage());
    }

    /**
     * @return ArrayObject<string>
     */
    public function getArgs(): ArrayObject
    {
        return $this->args;
    }
}
