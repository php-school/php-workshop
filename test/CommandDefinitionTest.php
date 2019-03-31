<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CommandArgument;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\CommandDefinition;

/**
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class CommandDefinitionTest extends TestCase
{

    public function testGettersSettersWithStringArgs() : void
    {
        $callable = function () {
        };
        $definition = new CommandDefinition('animal', ['name'], $callable);

        $this->assertSame($definition->getName(), 'animal');

        $requiredArgs = $definition->getRequiredArgs();

        $this->assertCount(1, $requiredArgs);
        $this->assertInstanceOf(CommandArgument::class, $requiredArgs[0]);
        $this->assertSame('name', $requiredArgs[0]->getName());
        $this->assertSame($callable, $definition->getCommandCallable());
    }

    public function testGettersSettersWithObjArgs() : void
    {
        $callable = function () {
        };
        $definition = new CommandDefinition('animal', [new CommandArgument('name')], $callable);

        $this->assertSame($definition->getName(), 'animal');

        $requiredArgs = $definition->getRequiredArgs();

        $this->assertCount(1, $requiredArgs);
        $this->assertInstanceOf(CommandArgument::class, $requiredArgs[0]);
        $this->assertSame('name', $requiredArgs[0]->getName());
        $this->assertSame($callable, $definition->getCommandCallable());
    }

    public function testExceptionIsThrowWhenTryingToAddRequiredArgAfterOptionalArg() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A required argument cannot follow an optional argument');

        $definition = new CommandDefinition('animal', [], 'strlen');
        $definition
            ->addArgument(CommandArgument::optional('optional-arg'))
            ->addArgument(CommandArgument::required('required-arg'));
    }

    public function testExceptionIsThrownWithWrongParameterToAddArgument() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $msg  = 'Parameter: "argument" can only be one of: "string", "PhpSchool\PhpWorkshop\CommandArgument" ';
        $msg .= 'Received: "stdClass"';

        $this->expectExceptionMessage($msg);
        $definition = new CommandDefinition('animal', [], 'strlen');
        $definition->addArgument(new \stdClass);
    }
}
