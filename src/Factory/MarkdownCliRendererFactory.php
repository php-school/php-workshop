<?php

namespace PhpSchool\PhpWorkshop\Factory;

use Colors\Color;
use Interop\Container\ContainerInterface;
use PhpSchool\CliMenu\Terminal\TerminalInterface;
use League\CommonMark\Block\Element\Document;
use League\CommonMark\Block\Element\FencedCode;
use League\CommonMark\Block\Element\Header;
use League\CommonMark\Block\Element\HorizontalRule;
use League\CommonMark\Block\Element\Paragraph;
use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Emphasis;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Strong;
use League\CommonMark\Inline\Element\Text;
use PhpSchool\PSX\Factory as PSXFactory;
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
 * Class MarkdownCliRendererFactory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MarkdownCliRendererFactory
{
    /**
     * @param ContainerInterface $c
     * @return CliRenderer
     */
    public function __invoke(ContainerInterface $c)
    {
        $terminal = $c->get(TerminalInterface::class);

        $highlighterFactory = new PSXFactory;
        $codeRender = new FencedCodeRenderer();
        $codeRender->addSyntaxHighlighter('php', new PhpHighlighter($highlighterFactory->__invoke()));

        $blockRenderers = [
            Document::class         => new DocumentRenderer,
            Header::class           => new HeaderRenderer,
            HorizontalRule::class   => new HorizontalRuleRenderer($terminal->getWidth()),
            Paragraph::class        => new ParagraphRenderer,
            FencedCode::class       => $codeRender,
        ];

        $inlineBlockRenderers = [
            Text::class             => new TextRenderer,
            Code::class             => new CodeRenderer,
            Emphasis::class         => new EmphasisRenderer,
            Strong::class           => new StrongRenderer,
            Newline::class          => new NewlineRenderer,
            Link::class             => new LinkRenderer,
        ];

        return new CliRenderer(
            $blockRenderers,
            $inlineBlockRenderers,
            $c->get(Color::class)
        );
    }
}
