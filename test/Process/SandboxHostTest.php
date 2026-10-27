<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\SandboxHost;
use PHPUnit\Framework\TestCase;

class SandboxHostTest extends TestCase
{
    public function testAddressDefaultsToLoopback(): void
    {
        static::assertSame('127.0.0.1', (new SandboxHost())->getAddress());
        static::assertSame('10.0.0.1', (new SandboxHost('10.0.0.1'))->getAddress());
    }

    public function testGetFreePortCanBeListenedOn(): void
    {
        $port = (new SandboxHost())->getFreePort();

        $server = stream_socket_server('tcp://127.0.0.1:' . $port);
        static::assertNotFalse($server);
        fclose($server);
    }
}
