<?php

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
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Static constructor to create from an instance of `PhpSchool\PhpWorkshop\Check\CheckInterface`.
     *
     * @param CheckInterface $check The check instance.
     * @return static The result.
     */
    public static function fromCheck(CheckInterface $check)
    {
        return new static($check->getName());
    }

    /**
     * Get the name of the check that this result was produced from.
     *
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
