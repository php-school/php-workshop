<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Markdown\Renderer;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;

final class ContextSpecificRenderer implements NodeRendererInterface
{
    private CurrentContext $currentContext;

    public function __construct(CurrentContext $currentContext)
    {
        $this->currentContext = $currentContext;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (!$node instanceof ContextSpecificBlock) {
            return '';
        }

        if ($this->currentContext->get() !== $node->getType()) {
            return '';
        }

        return $childRenderer->renderNodes($node->children());
    }
}
