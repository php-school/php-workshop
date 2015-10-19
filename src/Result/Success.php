<?php

namespace PhpSchool\PhpWorkshop\Result;

/**
 * Class Fail
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Success implements ResultInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
