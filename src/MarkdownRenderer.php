<?php

namespace PhpSchool\PhpWorkshop;

use League\CommonMark\DocParser;
use AydinHassan\CliMdRenderer\CliRenderer;

/**
 * Class MarkdownRenderer
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class MarkdownRenderer
{
    /**
     * @var \League\CommonMark\DocParser
     */
    private $docParser;

    /**
     * @var \AydinHassan\CliMdRenderer\CliRenderer
     */
    private $cliRenderer;

    /**
     * @param DocParser $docParser
     * @param CliRenderer $cliRenderer
     */
    public function __construct(DocParser $docParser, CliRenderer $cliRenderer)
    {
        $this->docParser = $docParser;
        $this->cliRenderer = $cliRenderer;
    }

    /**
     * @param string $markdown
     * @return string
     */
    public function render($markdown)
    {
        $ast = $this->docParser->parse($markdown);
        return $this->cliRenderer->renderBlock($ast);
    }
}
