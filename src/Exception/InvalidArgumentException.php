<?php

namespace PhpSchool\PhpWorkshop\Exception;

/**
 * Represents invalid argument exceptions.
 *
 * @package PhpSchool\PhpWorkshop\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Static constructor to create from the expected type & the actual value.
     *
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

    /**
     * Static constructor to create from when a parameter should be one of a set of allowed values, but was not.
     *
     * @param string $parameterName
     * @param mixed[] $allowedValues
     * @param mixed $actualValue
     * @return static
     */
    public static function notValidParameter($parameterName, array $allowedValues, $actualValue)
    {
        return new static(
            sprintf(
                'Parameter: "%s" can only be one of: "%s" Received: "%s"',
                $parameterName,
                static::stringify($allowedValues),
                static::stringify($actualValue)
            )
        );
    }

    /**
     * @param object $exercise
     * @param string $requiredInterface
     * @return static
     */
    public static function missingImplements($exercise, $requiredInterface)
    {
        return new static(
            sprintf(
                '"%s" is required to implement "%s", but it does not',
                get_class($exercise),
                $requiredInterface
            )
        );
    }

    /**
     * @param $value
     * @return string
     */
    private static function stringify($value)
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return implode('", "', array_map([static::class, 'stringify'], $value));
        }

        if (is_bool($value)) {
            return ($value) ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }


        return 'unknown';
    }
}
