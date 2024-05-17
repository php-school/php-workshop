<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\CliMdRenderer\CliRendererFactory;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use PHPUnit\Framework\TestCase;
use PhpSchool\PhpWorkshop\MarkdownRenderer;

class MarkdownRendererTest extends TestCase
{
    public function testRender(): void
    {
        $docParser      = new DocParser(Environment::createCommonMarkEnvironment());
        $cliRenderer    = (new CliRendererFactory())->__invoke();

        $renderer       = new MarkdownRenderer($docParser, $cliRenderer);

        $markdown       = "### HONEY BADGER DON'T CARE";
        $expected       = "\n[90m###[0m [36m[1mHONEY BADGER DON'T CARE[0m[0m\n\n";

        $this->assertSame($expected, $renderer->render($markdown));
    }
}
