<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Class CodeExecutionException
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeExecutionException extends RuntimeException
{

    /**
     * @var string
     */
    private $actual;
    /**
     * @var string
     */
    private $errors;

    /**
     * CodeExecutionException constructor.
     * @param string $reason
     * @param string $actual
     * @param string $errors
     */
    public function __construct($reason, $actual, $errors) {
        $this->message  = $reason;
        $this->actual   = $actual;
        $this->errors   = $errors;
    }

    /**
     * @param Process $process
     * @return static
     */
    public static function fromProcess(Process $process)
    {
        $message        = "PHP Code failed to execute. Error: \n%s";
        $processOutput  = $process->getOutput();
        $processErrorOutput  = $process->getErrorOutput();
        return new static(sprintf($message, $processErrorOutput ?: $processOutput), $processOutput, $processErrorOutput);
    }

    public function getActual()
    {
        return $this->actual;
    }

    public function getErrors()
    {
        return $this->errors;
    }

}
