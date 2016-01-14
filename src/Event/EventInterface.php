<?php

namespace PhpSchool\PhpWorkshop\Event;

/**
 * Class CliEvent
 * @package PhpSchool\PhpWorkshop\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface EventInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return mixed[]
     */
    public function getParameters();

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter($name);
}
