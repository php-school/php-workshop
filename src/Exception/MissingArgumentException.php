<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Represents the situation where a command was called without required parameters.
 */
class MissingArgumentException extends RuntimeException
{
    /**
     * @var array<string>
     */
    private $missingArguments = [];

    /**
     * Create the exception, requires the command name and missing arguments.
     *
     * @param string $commandName The command name.
     * @param array<string> $missingArguments An array of missing arguments.
     */
    public function __construct(string $commandName, array $missingArguments)
    {
        $this->missingArguments = $missingArguments;
        parent::__construct(
            sprintf(
                'Command: "%s" is missing the following arguments: "%s"',
                $commandName,
                implode('", "', $missingArguments)
            )
        );
    }

    /**
     * Retrieve the list of missing arguments.
     *
     * @return array<string>
     */
    public function getMissingArguments(): array
    {
        return $this->missingArguments;
    }
}
