<?php

namespace PhpSchool\PhpWorkshop\Markdown;

use AydinHassan\CliMdRenderer\CliRenderer;
use League\CommonMark\DocParser;

class LeagueCommonMarkRenderer implements Renderer
{
    /**
     * @var DocParser
     */
    private $docParser;

    /**
     * @var CliRenderer
     */
    private $cliRenderer;

    /**
     * Should be constructed with an instance of `MarkdownParser` with parses the markdown to an AST.
     * `CliRenderer` renders the AST to a string formatted for the console.
     *
     * @param DocParser $docParser
     * @param CliRenderer $cliRenderer
     */
    public function __construct(DocParser $docParser, CliRenderer $cliRenderer)
    {
        $this->docParser = $docParser;
        $this->cliRenderer = $cliRenderer;
    }

    /**
     * Expects a string of markdown and returns a string which has been formatted for
     * displaying on the console.
     *
     * @param string $markdown
     * @return string
     */
    public function render(string $markdown): string
    {
        $ast = $this->docParser->parse($markdown);
        return $this->cliRenderer->renderBlock($ast);
    }
}
