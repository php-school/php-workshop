<?php

namespace PhpSchool\PhpWorkshop\Result\Cli;

use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;
use PhpSchool\PhpWorkshop\Result\Failure;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class GenericFailure extends Failure implements FailureInterface
{

    /**
     * @var ArrayObject
     */
    private $args;

    /**
     * @var string
     */
    private static $name = 'CLI Program Runner';

    /**
     * @param ArrayObject $args
     * @param null $reason
     */
    public function __construct(ArrayObject $args, $reason = null)
    {
        $this->args = $args;
        parent::__construct(static::$name, $reason);
    }

    /**
     * Named constructor, for added code legibility.
     *
     * @param ArrayObject $args The arguments that caused the failure.
     * @param string|null $reason The reason (if any) of the failure.
     * @return static The result.
     */
    public static function fromArgsAndReason(ArrayObject $args, $reason)
    {
        return new static($args, $reason);
    }

    /**
     * Static constructor to create from a `PhpSchool\PhpWorkshop\Exception\CodeExecutionException` exception.
     *
     * @param ArrayObject $args The arguments that caused the failure.
     * @param CodeExecutionException $e The exception.
     * @return static The result.
     */
    public static function fromArgsAndCodeExecutionFailure(ArrayObject $args, CodeExecutionException $e)
    {
        return new static($args, $e->getMessage());
    }

    /**
     * @return ArrayObject
     */
    public function getArgs()
    {
        return $this->args;
    }
}
