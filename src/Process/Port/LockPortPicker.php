<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Process\Port;

use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * A random port, claimed with a lock so runs sharing a remote docker host never pick the same one, eg. with a
 * LockFactory on Redis.
 */
final class LockPortPicker implements PortPicker
{
    //as long as a sandbox container lives, so the port is free again when release() is never called, eg. a crash
    private const LOCK_SECONDS = 300;

    /**
     * @var array<int, LockInterface> port => its lock
     */
    private array $held = [];

    public function __construct(private LockFactory $locks) {}

    public function pick(): int
    {
        for ($try = 0; $try < 5; $try++) {
            $port = random_int(20000, 59999);

            //not released when this object goes, only by release() or when it expires
            $lock = $this->locks->createLock('php-school-port-' . $port, self::LOCK_SECONDS, false);
            if ($lock->acquire()) {
                $this->held[$port] = $lock;

                return $port;
            }
        }

        throw new RuntimeException('No free port to publish on');
    }

    public function release(int $port): void
    {
        ($this->held[$port] ?? null)?->release();
        unset($this->held[$port]);
    }
}
