<?php

namespace PhpSchool\PhpWorkshop\Event;

/**
 * An event representation.
 *
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface EventInterface
{
    /**
     * Get the name of this event.
     *
     * @return string
     */
    public function getName();

    /**
     * Get an array of parameters that were triggered with this event.
     *
     * @return mixed[]
     */
    public function getParameters();

    /**
     * Get a parameter by it's name.
     *
     * @param string $name The name of the parameter
     * @return mixed The value
     * @throws InvalidArgumentException If the parameter by name does not exist.
     */
    public function getParameter($name);
}
