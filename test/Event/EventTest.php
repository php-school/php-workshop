<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Class EventTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class EventTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $e = new Event('super-sweet-event!');
        $this->assertEquals('super-sweet-event!', $e->getName());
    }

    public function testGetParameters()
    {
        $e = new Event('super-sweet-event-with-cool-params', ['cool' => 'stuff']);
        $this->assertEquals('stuff', $e->getParameter('cool'));
        $this->assertEquals(['cool' => 'stuff'], $e->getParameters());
    }

    public function testExeceptionIsThrownIfParameterDoesNotExist()
    {
        $this->setExpectedException(InvalidArgumentException::class, 'Parameter: "cool" does not exist');
        $e = new Event('super-sweet-event-with-cool-params');
        $e->getParameter('cool');
    }
}
