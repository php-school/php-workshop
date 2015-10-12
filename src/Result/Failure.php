<?php

namespace PhpWorkshop\PhpWorkshop\Result;

/**
 * Class Failure
 * @package PhpWorkshop\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Failure implements ResultInterface
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $reason;

    /**
     * @param string $name
     * @param string $reason
     */
    public function __construct($name, $reason)
    {
        $this->reason = $reason;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return string
     */
    public function getCheckName()
    {
        return $this->name;
    }
}
