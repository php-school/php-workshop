<?php

namespace PhpSchool\PhpWorkshopTest;

use AydinHassan\CliMdRenderer\CliRenderer;
use AydinHassan\CliMdRenderer\CliRendererFactory;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use PHPUnit_Framework_TestCase;
use PhpSchool\PhpWorkshop\MarkdownRenderer;

/**
 * Class MarkdownRendererTest
 * @package PhpSchool\PhpWorkshopTest
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MarkdownRendererTest extends PHPUnit_Framework_TestCase
{
    public function testRender()
    {
        $docParser      = new DocParser(Environment::createCommonMarkEnvironment());
        $cliRenderer    = (new CliRendererFactory())->__invoke();

        $renderer       = new MarkdownRenderer($docParser, $cliRenderer);

        $markdown       = "### HONEY BADGER DON'T CARE";
        $expected       = "\n[90m###[0m [36m[1mHONEY BADGER DON'T CARE[0m[0m\n\n";

        $this->assertSame($expected, $renderer->render($markdown));
    }
}
