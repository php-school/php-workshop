<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Represents the situation where a command is called which does not exist in the framework.
 */
class CliRouteNotExistsException extends RuntimeException
{
    /**
     * @param string $routeName The name of the command which was typed.
     */
    public function __construct(string $routeName)
    {
        parent::__construct(sprintf('Command: "%s" does not exist', $routeName));
    }
}
