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
     * @param Process $process
     * @return static
     */
    public static function fromProcess(Process $process)
    {
        $message        = 'PHP Code failed to execute. Error: "%s"';
        $processOutput  = $process->getErrorOutput() ? $process->getErrorOutput() : $process->getOutput();
        return new static(sprintf($message, $processOutput));
    }
}
