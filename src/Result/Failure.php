<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpParser\Error as ParseErrorException;
use PhpSchool\PhpWorkshop\Check\CheckInterface;
use PhpSchool\PhpWorkshop\Exception\CodeExecutionException;

/**
 * Class Failure
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Failure implements FailureInterface
{
    use ResultTrait;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @param CheckInterface $check
     * @param string|null $reason
     */
    public function __construct(CheckInterface $check, $reason = null)
    {
        $this->check    = $check;
        $this->reason   = $reason;
    }

    /**
     * @param CheckInterface $check
     * @param string $reason
     * @return static
     */
    public static function withReason(CheckInterface $check, $reason)
    {
        return new static($check, $reason);
    }

    /**
     * @param CheckInterface $check
     * @param CodeExecutionException $e
     * @return static
     */
    public static function codeExecutionFailure(CheckInterface $check, CodeExecutionException $e)
    {
        return new static($check, $e->getMessage());
    }

    /**
     * @param CheckInterface $check
     * @param ParseErrorException $e
     * @param string $file
     * @return static
     */
    public static function codeParseFailure(CheckInterface $check, ParseErrorException $e, $file)
    {
        return new static($check, sprintf('File: "%s" could not be parsed. Error: "%s"', $file, $e->getMessage()));
    }

    /**
     * @return string|null
     */
    public function getReason()
    {
        return $this->reason;
    }
}
