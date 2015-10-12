<?php

namespace PhpWorkshop\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Class CliRouteNotExistsException
 * @package PhpWorkshop\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRouteNotExistsException extends RuntimeException
{
    /**
     * @param string $routeName
     */
    public function __construct($routeName)
    {
        parent::__construct(sprintf('Command: "%s" does not exist', $routeName));
    }
}
