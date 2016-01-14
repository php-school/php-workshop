<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\CliExecuteEvent;
use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * Class CliExecuteEventTest
 * @package PhpSchool\PhpWorkshopTest\Event
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CliExecuteEventTest extends PHPUnit_Framework_TestCase
{
    public function testAppendArg()
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $e->appendArg('4');
        $this->assertEquals([1, 2, 3, 4], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testPrependArg()
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $e->prependArg('4');
        $this->assertEquals([4, 1, 2, 3], $e->getArgs()->getArrayCopy());
        $this->assertNotSame($arr, $e->getArgs());
    }

    public function testGetArgs()
    {
        $arr = new ArrayObject([1, 2, 3]);
        $e = new CliExecuteEvent('event', $arr);

        $this->assertSame($arr, $e->getArgs());
    }
}
