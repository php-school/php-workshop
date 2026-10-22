<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshopTest\Asset;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;

/**
 * Concrete block, so that it can be partially mocked.
 */
class BlockStub extends AbstractBlock
{
    public function canContain(AbstractBlock $block): bool
    {
        return true;
    }

    public function isCode(): bool
    {
        return false;
    }

    public function matchesNextLine(Cursor $cursor): bool
    {
        return false;
    }
}
