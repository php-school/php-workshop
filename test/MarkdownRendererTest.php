<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshopTest\Asset\MarkdownFactory;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\MarkdownRenderer;

class MarkdownRendererTest extends TestCase
{
    private function renderer(): MarkdownRenderer
    {
        return new MarkdownRenderer(
            MarkdownFactory::parser(CurrentContext::cli()),
            MarkdownFactory::renderer(CurrentContext::cli()),
        );
    }

    public function testRender(): void
    {
        $markdown       = "### HONEY BADGER DON'T CARE";
        $expected       = "\n\e[90m###\e[0m \e[36m\e[1mHONEY BADGER DON'T CARE\e[0m\e[0m\n\n";

        $this->assertSame($expected, $this->renderer()->render($markdown));
    }

    public function testBoldAndEmphasisAreStyled(): void
    {
        $output = $this->renderer()->render('Some **bold**, *em* and _em2_ text');

        $this->assertStringNotContainsString('**', $output);
        $this->assertStringNotContainsString('*em*', $output);
        $this->assertStringNotContainsString('_em2_', $output);
        $this->assertStringContainsString("\e[1mbold\e[0m", $output);
        $this->assertStringContainsString("\e[3mem\e[0m", $output);
        $this->assertStringContainsString("\e[3mem2\e[0m", $output);
    }
}
