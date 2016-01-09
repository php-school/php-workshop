<?php

namespace PhpSchool\PhpWorkshop\Result;

use PhpSchool\PhpWorkshop\Check\CheckInterface;

/**
 * Class Fail
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success implements SuccessInterface
{
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param CheckInterface $check
     * @return static
     */
    public static function fromCheck(CheckInterface $check)
    {
        return new static($check->getName());
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
