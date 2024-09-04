<?php

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\ProcessInput;
use PHPUnit\Framework\TestCase;

class ProcessInputTest extends TestCase
{
    public function testProcessInput(): void
    {
        $input = new ProcessInput('composer', ['one', 'two'], __DIR__, ['SOME_VAR' => 'value'], [], 'input');

        static::assertSame('composer', $input->getExecutable());
        static::assertSame(['one', 'two'], $input->getArgs());
        static::assertSame(__DIR__, $input->getWorkingDirectory());
        static::assertSame(['SOME_VAR' => 'value'], $input->getEnv());
        static::assertSame([], $input->getExposedPorts());
        static::assertSame('input', $input->getInput());
    }
}
