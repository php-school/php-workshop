<?php

namespace PhpSchool\PhpWorkshopTest\Markdown;

use League\CommonMark\DocParser;
use PhpSchool\PhpWorkshop\Markdown\Renderer;
use PhpSchool\PhpWorkshopTest\ContainerAwareTest;

class RendererTest extends ContainerAwareTest
{
    public function testFactoryViaContainer(): void
    {
        $renderer = $this->container->get(Renderer::class);
        $this->assertInstanceOf(Renderer::class, $renderer);

        $markdown       = "### HONEY BADGER DON'T CARE";
        $expected       = "\n[90m###[0m [36m[1mHONEY BADGER DON'T CARE[0m[0m\n\n";

        $this->assertSame($expected, $renderer->render($markdown));
    }
}
