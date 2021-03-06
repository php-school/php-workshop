<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CommandArgument;
use PHPUnit\Framework\TestCase;

class CommandArgumentTest extends TestCase
{
    public function testRequiredArgument(): void
    {
        $arg = new CommandArgument('arg1');
        $this->assertSame('arg1', $arg->getName());
        $this->assertFalse($arg->isOptional());
    }

    public function testOptionalArgument(): void
    {
        $arg = new CommandArgument('arg1', true);
        $this->assertSame('arg1', $arg->getName());
        $this->assertTrue($arg->isOptional());
    }
}
