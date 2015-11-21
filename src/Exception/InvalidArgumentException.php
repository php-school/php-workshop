<?php

namespace PhpSchool\PhpWorkshop\Exception;

/**
 * Class InvalidArgumentException
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * @param string $expected
     * @param mixed $actual
     * @return static
     */
    public static function typeMisMatch($expected, $actual)
    {
        return new static(
            sprintf(
                'Expected: "%s" Received: "%s"',
                $expected,
                is_object($actual) ? get_class($actual) : gettype($actual)
            )
        );
    }
}
