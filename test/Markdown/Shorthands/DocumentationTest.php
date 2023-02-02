<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Code;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Newline;
use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Documentation;
use PHPUnit\Framework\TestCase;

class DocumentationTest extends TestCase
{
    public function testWithIncorrectArgs(): void
    {
        $doc = new Documentation();
        static::assertEmpty($doc->__invoke([]));
        static::assertEmpty($doc->__invoke([1]));
        static::assertEmpty($doc->__invoke([1, 2]));
    }

    public function testLink(): void
    {
        $doc = new Documentation();
        $nodes = $doc->__invoke(['DateTime', 'en', 'class.datetime.php']);

        static::assertCount(5, $nodes);

        static::assertInstanceOf(Text::class, $nodes[0]);
        static::assertEquals('Documentation on ', $nodes[0]->getContent());
        static::assertInstanceOf(Code::class, $nodes[1]);
        static::assertEquals('DateTime', $nodes[1]->getContent());
        static::assertInstanceOf(Text::class, $nodes[2]);
        static::assertEquals(' can be found by pointing your browser here:', $nodes[2]->getContent());
        static::assertInstanceOf(Newline::class, $nodes[3]);
        static::assertInstanceOf(Link::class, $nodes[4]);
        static::assertEquals('https://php.net/manual/en/class.datetime.php', $nodes[4]->getUrl());
    }

    public function testMultipleLinks(): void
    {
        $doc = new Documentation();
        $nodes = $doc->__invoke(['DateTime', 'en', 'class.datetime.php', 'datetime.format.php']);

        static::assertCount(6, $nodes);

        static::assertInstanceOf(Text::class, $nodes[0]);
        static::assertEquals('Documentation on ', $nodes[0]->getContent());
        static::assertInstanceOf(Code::class, $nodes[1]);
        static::assertEquals('DateTime', $nodes[1]->getContent());
        static::assertInstanceOf(Text::class, $nodes[2]);
        static::assertEquals(' can be found by pointing your browser here:', $nodes[2]->getContent());
        static::assertInstanceOf(Newline::class, $nodes[3]);
        static::assertInstanceOf(Link::class, $nodes[4]);
        static::assertEquals('https://php.net/manual/en/class.datetime.php', $nodes[4]->getUrl());
        static::assertInstanceOf(Link::class, $nodes[5]);
        static::assertEquals('https://php.net/manual/en/datetime.format.php', $nodes[5]->getUrl());
    }
}
