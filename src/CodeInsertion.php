<?php

namespace PhpSchool\PhpWorkshop;

use Assert\Assertion;

/**
 * Class CodeInsertion.
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeInsertion
{
    const TYPE_BEFORE   = 'before';
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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
