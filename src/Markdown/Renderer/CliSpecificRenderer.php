<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;

final class CliSpecificRenderer implements ContextSpecificRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $renderer, bool $inTightList = false): string
    {
        if (!$block instanceof ContextSpecificBlock) {
            /** @var iterable<AbstractInline> $children */
            $children = $block->children();
            return $renderer->renderInlines($children);
        }

        if (ContextSpecificBlockParser::CLI_TYPE !== $block->getType()) {
            return '';
        }

        return $renderer->renderInlines($block->children());
    }
}
