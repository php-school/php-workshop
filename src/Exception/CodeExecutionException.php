<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Represents the situation where some PHP code could not be executed successfully.
 */
class CodeExecutionException extends RuntimeException
{
    /**
     * Static constructor to create an instance from a failed `Symfony\Component\Process\Process` instance.
     *
     * @param Process $process The `Symfony\Component\Process\Process` instance which failed.
     * @return self
     */
    public static function fromProcess(Process $process)
    {
        return new self(
            sprintf(
                'PHP Code failed to execute. Error: "%s"',
                trim($process->getErrorOutput() ?: $process->getOutput())
            )
        );
    }
}
