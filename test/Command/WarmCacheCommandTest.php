<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Command;

use PhpSchool\PhpWorkshop\Command\WarmCacheCommand;
use PhpSchool\PhpWorkshop\ReferencePackages;
use PHPUnit\Framework\TestCase;

class WarmCacheCommandTest extends TestCase
{
    public function testWarmsTheReferencePackages(): void
    {
        $referencePackages = $this->createMock(ReferencePackages::class);
        $referencePackages->expects($this->once())->method('warm');

        $this->assertSame(0, (new WarmCacheCommand($referencePackages))->__invoke());
    }
}
