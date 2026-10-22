<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Parser;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\AbstractBlockContinueParser;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;

/**
 * Keeps a context specific block open, containing any other blocks, until its closing tag.
 */
final class ContextSpecificBlockContinueParser extends AbstractBlockContinueParser
{
    private ContextSpecificBlock $block;

    public function __construct(string $type)
    {
        $this->block = new ContextSpecificBlock($type);
    }

    public function getBlock(): ContextSpecificBlock
    {
        return $this->block;
    }

    public function isContainer(): bool
    {
        return true;
    }

    public function canContain(AbstractBlock $childBlock): bool
    {
        return true;
    }

    public function tryContinue(Cursor $cursor, BlockContinueParserInterface $activeBlockParser): BlockContinue
    {
        $cursor->advanceToNextNonSpaceOrTab();

        if ($cursor->match(ContextSpecificBlockParser::getEndBlockRegex($this->block->getType())) !== null) {
            return BlockContinue::finished();
        }

        return BlockContinue::at($cursor);
    }
}
