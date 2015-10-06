<?php

namespace PhpWorkshop\PhpWorkshopTest;

use PHPUnit_Framework_TestCase;
use PhpWorkshop\PhpWorkshop\CommandDefinition;

/**
 * Class CommandDefinitionTest
 * @package PhpWorkshop\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandDefinitionTest extends PHPUnit_Framework_TestCase
{

    public function testGettersSetters()
    {
        $callable = function () {};
        $definition = new CommandDefinition('animal', ['name'], $callable);

        $this->assertSame($definition->getName(), 'animal');
        $this->assertSame(['name'], $definition->getRequiredArgs());
        $this->assertSame($callable, $definition->getCommandCallable());
    }
}

