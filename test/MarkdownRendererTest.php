<?php

namespace PhpSchool\PhpWorkshopTest;

use League\CommonMark\Parser\MarkdownParser;
use PhpSchool\PhpWorkshop\Factory\MarkdownEnvironmentFactory;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use PhpSchool\CliMdRenderer\CliRenderer;
use Colors\Color;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\MarkdownRenderer;

class MarkdownRendererTest extends TestCase
{
    private function renderer(): MarkdownRenderer
    {
        $color = new Color();
        $color->setForceStyle(true);

        return new MarkdownRenderer(
            new MarkdownParser((new MarkdownEnvironmentFactory())->create(80, new ContextSpecificRenderer(CurrentContext::cli()), [])),
            new CliRenderer((new MarkdownEnvironmentFactory())->create(80, new ContextSpecificRenderer(CurrentContext::cli()), []), $color),
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

    public function testIndentationIsKeptInsideContextSpecificBlocks(): void
    {
        $markdown = "{{ cli }}\n```\nfunction a() {\n    return 1;\n}\n```\n{{ cli }}";

        $this->assertStringContainsString('        return 1;', $this->renderer()->render($markdown));
    }
}
