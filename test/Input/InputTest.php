<?php

namespace PhpSchool\PhpWorkshopTest\Input;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Input\Input;
use PHPUnit\Framework\TestCase;

/**
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InputTest extends TestCase
{
    public function testGetAppName(): void
    {
        $input = new Input('app');
        $this->assertEquals('app', $input->getAppName());
    }

    public function testGetArgument(): void
    {
        $input = new Input('app', ['arg1' => 'some-value']);
        $this->assertEquals('some-value', $input->getArgument('arg1'));
    }

    public function testGetArgumentThrowsExceptionIfArgumentNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Argument with name: "arg1" does not exist');

        $input = new Input('app');
        $input->getArgument('arg1');
    }

    public function testHasArgument(): void
    {
        $input = new Input('app', ['arg1' => 'some-value']);
        $this->assertTrue($input->hasArgument('arg1'));
        $this->assertFalse($input->hasArgument('arg2'));
    }

    public function testSetArgument(): void
    {
        $input = new Input('app');
        $this->assertFalse($input->hasArgument('arg1'));
        $input->setArgument('arg1', 'some-value');
        $this->assertEquals('some-value', $input->getArgument('arg1'));
    }
}
