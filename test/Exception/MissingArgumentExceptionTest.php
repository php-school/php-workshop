<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\MissingArgumentException;

class MissingArgumentExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new MissingArgumentException('some-route', ['arg1', 'arg2']);
        $this->assertEquals(
            'Command: "some-route" is missing the following arguments: "arg1", "arg2"',
            $e->getMessage(),
        );

        $this->assertSame(['arg1', 'arg2'], $e->getMissingArguments());
    }
}
