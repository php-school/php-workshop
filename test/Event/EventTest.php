<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\Event;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testGetName(): void
    {
        $e = new Event('super-sweet-event!');
        $this->assertEquals('super-sweet-event!', $e->getName());
    }

    public function testGetParameters(): void
    {
        $e = new Event('super-sweet-event-with-cool-params', ['cool' => 'stuff']);
        $this->assertEquals('stuff', $e->getParameter('cool'));
        $this->assertEquals(['cool' => 'stuff'], $e->getParameters());
    }

    public function testExeceptionIsThrownIfParameterDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter: "cool" does not exist');
        $e = new Event('super-sweet-event-with-cool-params');
        $e->getParameter('cool');
    }
}
