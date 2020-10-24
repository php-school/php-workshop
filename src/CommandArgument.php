<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

/**
 * Command argument definition
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
    public function __construct(string $name, bool $optional = false)
    {
        $this->name = $name;
        $this->optional = $optional;
    }

    /**
     * @param string $name
     * @return self
     */
    public static function optional(string $name): self
    {
        return new self($name, true);
    }

    /**
     * @param string $name
     * @return self
     */
    public static function required(string $name): self
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return !$this->isOptional();
    }

    /**
     * @return bool
     */
    public function isOptional(): bool
    {
        return $this->optional;
    }
}
