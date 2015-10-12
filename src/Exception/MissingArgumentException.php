<?php

namespace PhpWorkshop\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Class MissingArgumentException
 * @package PhpWorkshop\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingArgumentException extends RuntimeException
{
    /**
     * @param $commandName
     * @param array $missingArguments
     */
    public function __construct($commandName, array $missingArguments)
    {
        parent::__construct(
            sprintf(
                'Command: "%s" is missing the following arguments: "%s"',
                $commandName,
                implode('", "', $missingArguments)
            )
        );
    }
}
