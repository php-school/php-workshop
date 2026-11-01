<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use Symfony\Component\Lock\LockFactory;

/**
 * A random port, claimed with a lock so runs sharing a remote docker host never pick the same one, eg. with a
 * LockFactory on Redis.
 */
final class LockPortPicker implements PortPicker
{
    //longer than a run can take, then the port is free again
    private const LOCK_SECONDS = 60;

    public function __construct(private LockFactory $locks) {}

    public function pick(): int
    {
        for ($try = 0; $try < 100; $try++) {
            $port = random_int(20000, 59999);

            //not released when this object goes, it expires
            if ($this->locks->createLock('php-school-port-' . $port, self::LOCK_SECONDS, false)->acquire()) {
                return $port;
            }
        }

        throw new RuntimeException('No free port to publish on');
    }
}
