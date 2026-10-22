<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Renderer;

use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\PhpWorkshop\MarkdownRenderer;
use PhpSchool\PhpWorkshopTest\Asset\MarkdownFactory;
use PHPUnit\Framework\TestCase;

class ContextSpecificRendererTest extends TestCase
{
    public function testRenderWithWrongBlock(): void
    {
        $childRenderer = $this->createMock(ChildNodeRendererInterface::class);
        $childRenderer->expects($this->never())->method('renderNodes');

        $renderer = new ContextSpecificRenderer(CurrentContext::cli());

        static::assertEquals('', $renderer->render($this->createMock(Node::class), $childRenderer));
    }

    public function testRenderIgnoresContextsNotMatchingTheCurrentContext(): void
    {
        $childRenderer = $this->createMock(ChildNodeRendererInterface::class);
        $childRenderer->expects($this->never())->method('renderNodes');

        $renderer = new ContextSpecificRenderer(CurrentContext::cli());

        static::assertEquals('', $renderer->render(new ContextSpecificBlock('cloud'), $childRenderer));
    }

    public function testChildBlocksAreRenderedIfContextMatchesTheCurrentContext(): void
    {
        $paragraph = new Paragraph();
        $block = new ContextSpecificBlock('cli');
        $block->appendChild($paragraph);

        $childRenderer = $this->createMock(ChildNodeRendererInterface::class);
        $childRenderer->expects($this->once())
            ->method('renderNodes')
            ->with([$paragraph])
            ->willReturn('Some content');

        $renderer = new ContextSpecificRenderer(CurrentContext::cli());

        static::assertEquals('Some content', $renderer->render($block, $childRenderer));
    }

    public function testOnlyMatchingContextIsRenderedEndToEnd(): void
    {
        $markdown = "Intro\n\n{{ cli }}\nCLI ONLY\n{{ cli }}\n\n{{ cloud }}\nCLOUD ONLY\n{{ cloud }}\n\nOutro\n";

        $render = function (CurrentContext $context) use ($markdown): string {
            return (new MarkdownRenderer(
                MarkdownFactory::parser($context),
                MarkdownFactory::renderer($context),
            ))->render($markdown);
        };

        $cli = $render(CurrentContext::cli());
        static::assertStringContainsString('CLI ONLY', $cli);
        static::assertStringNotContainsString('CLOUD ONLY', $cli);
        static::assertStringContainsString('Intro', $cli);
        static::assertStringContainsString('Outro', $cli);

        $cloud = $render(CurrentContext::cloud());
        static::assertStringContainsString('CLOUD ONLY', $cloud);
        static::assertStringNotContainsString('CLI ONLY', $cloud);
    }
}
