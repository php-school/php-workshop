<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CommandArgument;
use PHPUnit_Framework_TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandArgumentTest extends PHPUnit_Framework_TestCase
{
    public function testRequiredArgument()
    {
        $arg = new CommandArgument('arg1');
        $this->assertSame('arg1', $arg->getName());
        $this->assertFalse($arg->isOptional());
    }

    public function testOptionalArgument()
    {
        $arg = new CommandArgument('arg1', true);
        $this->assertSame('arg1', $arg->getName());
        $this->assertTrue($arg->isOptional());
    }
}
