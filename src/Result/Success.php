<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Default implementation of `PhpSchool\PhpWorkshop\Result\SuccessInterface`.
 */
class Success implements SuccessInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name The name of the check that produced this result.
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Static constructor to create from an instance of `PhpSchool\PhpWorkshop\Check\CheckInterface`.
     *
     * @param CheckInterface $check The check instance.
     * @return self The result.
     */
    public static function fromCheck(CheckInterface $check): self
    {
        return new self($check->getName());
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName(): string
    {
        return $this->name;
    }
}
