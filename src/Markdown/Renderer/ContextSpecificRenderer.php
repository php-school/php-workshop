<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;

final class ContextSpecificRenderer implements BlockRendererInterface
{
    /**
     * @var CurrentContext
     */
    private $currentContext;

    public function __construct(CurrentContext $currentContext)
    {
        $this->currentContext = $currentContext;
    }

    public function render(AbstractBlock $block, ElementRendererInterface $renderer, bool $inTightList = false): string
    {
        if (!$block instanceof ContextSpecificBlock) {
            return '';
        }

        if ($this->currentContext->get() !== $block->getType()) {
            return '';
        }

        /** @var iterable<AbstractBlock> $children */
        $children = $block->children();
        return $renderer->renderBlocks($children);
    }
}
