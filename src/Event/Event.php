<?php

namespace PhpSchool\PhpWorkshop\Event;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;

/**
 * Class CliEvent
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Event implements EventInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param string $name
     * @param array $parameters
     */
    public function __construct($name, array $parameters = [])
    {
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed[]
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!array_key_exists($name, $this->parameters)) {
            throw new InvalidArgumentException(sprintf('Parameter: "%s" does not exist', $name));
        }

        return $this->parameters[$name];
    }
}
