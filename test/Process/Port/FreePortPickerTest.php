<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Process\Port;

use PhpSchool\PhpWorkshop\Process\Port\FreePortPicker;
use PHPUnit\Framework\TestCase;

class FreePortPickerTest extends TestCase
{
    public function testPortCanBeListenedOn(): void
    {
        $server = stream_socket_server('tcp://127.0.0.1:' . (new FreePortPicker())->pick());
        static::assertNotFalse($server);
        fclose($server);
    }
}
