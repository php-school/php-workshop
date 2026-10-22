<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Asset;

/**
 * Mockable callable, used as a listener/callback double in tests.
 */
class CallableStub
{
    public function __invoke(mixed ...$arguments): mixed
    {
        return null;
    }

    public function doNotInvokeMe(): void {}
}
