<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\Exception\CliRouteNotExistsException;

class CliRouteNotExistsTest extends TestCase
{
    public function testException(): void
    {
        $e = new CliRouteNotExistsException('some-route');
        $this->assertEquals('Command: "some-route" does not exist', $e->getMessage());
    }
}
