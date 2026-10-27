<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Command;

use PhpSchool\PhpWorkshop\ReferencePackages;

/**
 * Run by the workshop's composer post-install-cmd, so verifies can install reference packages offline.
 */
class WarmCacheCommand
{
    public function __construct(private ReferencePackages $referencePackages) {}

    public function __invoke(): int
    {
        $this->referencePackages->warm();

        return 0;
    }
}
