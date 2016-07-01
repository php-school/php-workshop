<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Represents the situation where some PHP code could not be executed successfully.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeExecutionException extends RuntimeException
{
    /**
     * Static constructor to create an instance from a failed `Process` instance.
     *
     * @param Process $process The `Process` instance which failed.
     * @return static
     */
    public static function fromProcess(Process $process)
    {
        $message        = 'PHP Code failed to execute. Error: "%s"';
        $processOutput  = $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
        return new static(sprintf($message, $processOutput));
    }
}
