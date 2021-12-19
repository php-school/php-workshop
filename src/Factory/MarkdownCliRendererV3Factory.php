<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Factory;

use AydinHassan\CliMdRenderer\Renderer\ListBlockRenderer;
use AydinHassan\CliMdRenderer\Renderer\ListItemRenderer;
use Colors\Color;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text;
use Psr\Container\ContainerInterface;
use Kadet\Highlighter\KeyLighter;
use PhpSchool\Terminal\Terminal;
use AydinHassan\CliMdRenderer\Highlighter\PhpHighlighter;
use AydinHassan\CliMdRenderer\InlineRenderer\CodeRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\EmphasisRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\LinkRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\NewlineRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\StrongRenderer;
use AydinHassan\CliMdRenderer\InlineRenderer\TextRenderer;
use AydinHassan\CliMdRenderer\Renderer\DocumentRenderer;
use AydinHassan\CliMdRenderer\Renderer\FencedCodeRenderer;
use AydinHassan\CliMdRenderer\Renderer\HeaderRenderer;
use AydinHassan\CliMdRenderer\Renderer\HorizontalRuleRenderer;
use AydinHassan\CliMdRenderer\Renderer\ParagraphRenderer;
use AydinHassan\CliMdRenderer\CliRenderer;

/**
 * Factory for CliRenderer
 */
class MarkdownCliRendererV3Factory
{
    public function __invoke(ContainerInterface $c): CliRenderer
    {
        $terminal = $c->get(Terminal::class);

        $codeRender = new FencedCodeRenderer();
        $codeRender->addSyntaxHighlighter('php', new PhpHighlighter(new KeyLighter()));

        $blockRenderers = [
            Document::class         => new DocumentRenderer(),
            Heading::class          => new HeaderRenderer(),
            ThematicBreak::class    => new HorizontalRuleRenderer($terminal->getWidth()),
            Paragraph::class        => new ParagraphRenderer(),
            FencedCode::class       => $codeRender,
            ListBlock::class        => new ListBlockRenderer(),
            ListItem::class         => new ListItemRenderer(),
        ];

        $inlineBlockRenderers = [
            Text::class             => new TextRenderer(),
            Code::class             => new CodeRenderer(),
            Emphasis::class         => new EmphasisRenderer(),
            Strong::class           => new StrongRenderer(),
            Newline::class          => new NewlineRenderer(),
            Link::class             => new LinkRenderer(),
        ];

        return new CliRenderer(
            $blockRenderers,
            $inlineBlockRenderers,
            $c->get(Color::class)
        );
    }
}
