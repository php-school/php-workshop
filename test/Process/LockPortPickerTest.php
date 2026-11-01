<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Process;

use PhpSchool\PhpWorkshop\Process\LockPortPicker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\InMemoryStore;

class LockPortPickerTest extends TestCase
{
    public function testPickedPortIsHighAndLockedUntilItExpires(): void
    {
        $locks = new LockFactory(new InMemoryStore());

        $port = (new LockPortPicker($locks))->pick();

        static::assertGreaterThanOrEqual(20000, $port);
        static::assertLessThanOrEqual(59999, $port);
        //another run can not take it
        static::assertFalse($locks->createLock('php-school-port-' . $port)->acquire());
    }
}
