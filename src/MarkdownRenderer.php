<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use League\CommonMark\Parser\MarkdownParserInterface;
use PhpSchool\CliMdRenderer\CliRenderer;

/**
 * Utility to render a markdown string to a string formatted with ANSI escape codes for output
 * on the console.
 */
class MarkdownRenderer
{
    /**
     * @var MarkdownParserInterface
     */
    private $docParser;

    /**
     * @var CliRenderer
     */
    private $cliRenderer;

    /**
     * Should be constructed with an instance of `MarkdownParserInterface` with parses the markdown to an AST.
     * `CliRenderer` renders the AST to a string formatted for the console.
     *
     * @param MarkdownParserInterface $docParser
     * @param CliRenderer $cliRenderer
     */
    public function __construct(MarkdownParserInterface $docParser, CliRenderer $cliRenderer)
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
