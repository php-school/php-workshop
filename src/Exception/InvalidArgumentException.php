<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Exception;

/**
 * Represents invalid argument exceptions.
 */
class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Static constructor to create an exception when using string doesn't match an array
     * of options.
     *
     * @param string $needle
     * @param array<string> $haystack
     * @return self
     */
    public static function notInArray(string $needle, array $haystack): self
    {
        return new self(
            sprintf(
                'Value "%s" is not an element of the valid values: %s',
                $needle,
                implode(', ', $haystack)
            )
        );
    }

    /**
     * Static constructor to create from the expected type & the actual value.
     *
     * @param string $expected
     * @param mixed $actual
     * @return self
     */
    public static function typeMisMatch(string $expected, $actual): self
    {
        return new self(
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
     * @return self
     */
    public static function notValidParameter(string $parameterName, array $allowedValues, $actualValue): self
    {
        return new self(
            sprintf(
                'Parameter: "%s" can only be one of: "%s" Received: "%s"',
                $parameterName,
                static::stringify($allowedValues),
                static::stringify($actualValue)
            )
        );
    }

    /**
     * @param object $object
     * @param class-string $requiredInterface
     * @return self
     */
    public static function missingImplements(object $object, string $requiredInterface): self
    {
        return new self(
            sprintf(
                '"%s" is required to implement "%s", but it does not',
                get_class($object),
                $requiredInterface
            )
        );
    }

    /**
     * @param mixed $value
     * @return string
     */
    private static function stringify($value): string
    {
        if (is_object($value)) {
            return get_class($value);
        }

        if (is_array($value)) {
            return implode('", "', array_map([self::class, 'stringify'], $value));
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
