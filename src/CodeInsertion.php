<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;

/**
 * This class is a simple DTO to represent a code insertion which should
 * be performed on a students solution.
 *
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeInsertion
{
    /**
     * Denotes that the block of code this insertion
     * represents, should be inserted at the top of the solution.
     */
    const TYPE_BEFORE   = 'before';

    /**
     * Denotes that the block of code this insertion
     * represents, should be inserted at the bottom of the solution.
     */
    const TYPE_AFTER    = 'after';

    /**
     * @var array
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
     * Accepts the type on insertion, either static::TYPE_BEFORE or static::TYPE_AFTER
     * and a string containing the code to be inserted.
     *
     * @param string $type
     * @param string $code
     */
    public function __construct($type, $code)
    {
        Assertion::inArray($type, $this->types);
        Assertion::string($code);
        
        $this->type = $type;
        $this->code = $code;
    }

    /**
     * Get the type of insertion, either static::TYPE_BEFORE or static::TYPE_AFTER.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get a string containing the code be inserted
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
