<?php

namespace PhpSchool\PhpWorkshopTest\Event;

use PhpSchool\PhpWorkshop\Event\ContainerListenerHelper;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ContainerListenerHelperTest extends TestCase
{
    public function testDefaultMethodIsInvoke()
    {
        $helper = new ContainerListenerHelper('Some\Object');

        $this->assertEquals('Some\Object', $helper->getService());
        $this->assertEquals('__invoke', $helper->getMethod());
    }

    public function testWithCustomMethod()
    {
        $helper = new ContainerListenerHelper('Some\Object', 'myMethod');

        $this->assertEquals('Some\Object', $helper->getService());
        $this->assertEquals('myMethod', $helper->getMethod());
    }
}
