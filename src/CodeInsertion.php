<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * This class is a simple DTO to represent a code insertion which should
 * be performed on a students solution.
 */
class CodeInsertion
{
    /**
     * Denotes that the block of code this insertion
     * represents, should be inserted at the top of the solution.
     */
    public const TYPE_BEFORE   = 'before';

    /**
     * Denotes that the block of code this insertion
     * represents, should be inserted at the bottom of the solution.
     */
    public const TYPE_AFTER    = 'after';

    /**
     * @var array<string>
     */
    private $types = [
        self::TYPE_BEFORE,
        self::TYPE_AFTER,
    ];

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $code;

    /**
     * Accepts the type of insertion, either `static::TYPE_BEFORE` or `static::TYPE_AFTER`
     * and a string containing the code to be inserted.
     *
     * @param string $type
     * @param string $code
     */
    public function __construct(string $type, string $code)
    {
        if (!in_array($type, $this->types, true)) {
            throw new InvalidArgumentException(
                sprintf('Value "%s" is not an element of the valid values: %s', $type, implode(', ', $this->types)),
            );
        }

        $this->type = $type;
        $this->code = $code;
    }

    /**
     * Get the type of insertion, either `static::TYPE_BEFORE` or `static::TYPE_AFTER`.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get a string containing the code be inserted.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
