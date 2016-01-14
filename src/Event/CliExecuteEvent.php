<?php

namespace PhpSchool\PhpWorkshop\Event;

use Assert\Assertion;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;

/**
 * Class CliEvent
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliExecuteEvent extends Event
{
    /**
     * @var ArrayObject
     */
    private $args;

    /**
     * @param string $name
     * @param ArrayObject $args
     */
    public function __construct($name, ArrayObject $args)
    {
        parent::__construct($name, ['args' => $args]);
        $this->args = $args;
    }

    /**
     * @param string $arg
     */
    public function prependArg($arg)
    {
        Assertion::string($arg);
        $this->args = $this->args->prepend($arg);
    }

    /**
     * @param string $arg
     */
    public function appendArg($arg)
    {
        Assertion::string($arg);
        $this->args = $this->args->append($arg);
    }

    /**
     * @return ArrayObject
     */
    public function getArgs()
    {
        return $this->args;
    }
}
