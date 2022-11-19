<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Renderer;

use AydinHassan\CliMdRenderer\CliRenderer;
use AydinHassan\CliMdRenderer\Renderer\CliBlockRendererInterface;
use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;

final class CliSpecificRenderer implements ContextSpecificRendererInterface
{
    public function render(AbstractBlock $block, ElementRendererInterface $renderer, bool $inTightList = false): string
    {
        if (!$block instanceof ContextSpecificBlock) {
            return $renderer->renderInlines($block->children());
        }

        if (ContextSpecificBlock::CLI_TYPE !== $block->getType()) {
            return '';
        }
        
        return $renderer->renderInlines($block->children());
    }
}
