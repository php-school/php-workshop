<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CliExecuteEventTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliExecuteEventTest extends TestCase
{
    public function testAppendArg() : void
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $e->appendArg('4');
        $this->assertEquals([1, 2, 3, 4], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testPrependArg() : void
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $e->prependArg('4');
        $this->assertEquals([4, 1, 2, 3], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testGetArgs() : void
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $this->assertSame($arr, $e->getArgs());
    }
}
