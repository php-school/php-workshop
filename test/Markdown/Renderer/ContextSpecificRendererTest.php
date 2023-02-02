<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\ElementRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PHPUnit\Framework\TestCase;

class ContextSpecificRendererTest extends TestCase
{
    public function testRenderWithWrongBlock(): void
    {
        $htmlRenderer = $this->getMockForAbstractClass(ElementRendererInterface::class);
        $block = $this->getMockForAbstractClass(AbstractBlock::class);

        $currentContext = CurrentContext::cli();
        $renderer = new ContextSpecificRenderer($currentContext);
        $renderer->render($block, $htmlRenderer);

        static::assertEquals('', $renderer->render($block, $htmlRenderer));
    }

    public function testRenderIgnoresContextsNotMatchingTheCurrentContext(): void
    {
        $htmlRenderer = $this->getMockForAbstractClass(ElementRendererInterface::class);
        $block = new ContextSpecificBlock('cloud');

        $currentContext = CurrentContext::cli();
        $renderer = new ContextSpecificRenderer($currentContext);
        $renderer->render($block, $htmlRenderer);

        static::assertEquals('', $renderer->render($block, $htmlRenderer));
    }

    public function testChildBlocksAreRenderedIfContextMatchesTheCurrentContext(): void
    {
        $paragraph = new Paragraph();
        $paragraph->addLine('Some content');
        $htmlRenderer = $this->getMockForAbstractClass(ElementRendererInterface::class);
        $htmlRenderer->expects($this->any())
            ->method('renderBlocks')
            ->with([$paragraph])
            ->willReturn('<p>Some content</p>');

        $block = new ContextSpecificBlock('cli');
        $block->appendChild($paragraph);

        $currentContext = CurrentContext::cli();
        $renderer = new ContextSpecificRenderer($currentContext);
        $renderer->render($block, $htmlRenderer);

        static::assertEquals('<p>Some content</p>', $renderer->render($block, $htmlRenderer));
    }
}
