<?php

namespace PhpSchool\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Class MissingArgumentException
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
     * @param $commandName
     * @param array $missingArguments
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
     * @return array
     */
    public function getMissingArguments()
    {
        return $this->missingArguments;
    }
}
