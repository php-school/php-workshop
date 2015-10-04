<?php

namespace PhpWorkshop\PhpWorkshop\Exception;

use RuntimeException;

/**
 * Class CliRouteNotExists
 * @package PhpWorkshop\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliRouteNotExists extends RuntimeException
{
    /**
     * @param string $routeName
     */
    public function __construct($routeName)
    {
        parent::__construct('Command: "%s" does not exist', $routeName);
    }
}
