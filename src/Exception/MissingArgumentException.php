<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Represents the situation where a command was called without required parameters.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingArgumentException extends RuntimeException
{
    /**
     * @var array
     */
    private $missingArguments = [];

    /**
     * Create the exception, requires the command name and missing arguments.
     *
     * @param string $commandName The command name.
     * @param array $missingArguments An array of missing arguments (strings)
     */
    public function __construct($commandName, array $missingArguments)
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
     * @return array
     */
    public function getMissingArguments()
    {
        return $this->missingArguments;
    }
}
