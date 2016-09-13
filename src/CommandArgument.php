<?php

namespace PhpSchool\PhpWorkshop;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandArgument
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $optional;

    /**
     * @param string $name The name of the argument
     * @param bool $optional Whether it is required or not
     */
    public function __construct($name, $optional = false)
    {
        $this->name = $name;
        $this->optional = $optional;
    }

    /**
     * @param string $name
     * @return static
     */
    public static function optional($name)
    {
        return new static($name, true);
    }

    /**
     * @param string $name
     * @return static
     */
    public static function required($name)
    {
        return new static($name);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return !$this->isOptional();
    }

    /**
     * @return bool
     */
    public function isOptional()
    {
        return $this->optional;
    }
}
