<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;
use PhpSchool\PhpWorkshop\Process\SandboxExecution;

/**
 * Represents the situation where some PHP code could not be executed successfully.
 */
class CodeExecutionException extends RuntimeException
{
    /**
     * Static constructor to create an instance from a failed sandbox execution.
     *
     * @return self
     */
    public static function fromExecution(SandboxExecution $execution): self
    {
        return new self(
            sprintf(
                'PHP Code failed to execute. Error: "%s"',
                trim($execution->getErrorOutput() ?: $execution->getOutput()),
            ),
        );
    }
}
