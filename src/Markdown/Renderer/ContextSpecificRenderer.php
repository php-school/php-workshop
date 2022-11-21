<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;

final class ContextSpecificRenderer implements BlockRendererInterface
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function render(AbstractBlock $block, ElementRendererInterface $renderer, bool $inTightList = false): string
    {
        if (!$block instanceof ContextSpecificBlock) {
            /** @var iterable<AbstractInline> $children */
            $children = $block->children();
            return $renderer->renderInlines($children);
        }

        if ($this->type !== $block->getType()) {
            return '';
        }

        return $renderer->renderInlines($block->children());
    }
}
