<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Block;

use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Block\Paragraph;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshopTest\Asset\MarkdownFactory;
use PHPUnit\Framework\TestCase;

class ContextSpecificBlockTest extends TestCase
{
    public function testExceptionIsThrownIfConstructedWithInvalidType(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Parameter: "type" can only be one of: "cli", "cloud" Received: "invalid"');

        new ContextSpecificBlock('invalid');
    }

    public function testGetType(): void
    {
        $block = new ContextSpecificBlock('cli');
        static::assertEquals('cli', $block->getType());
    }

    public function testBlockContainsFollowingBlocksUntilEndTag(): void
    {
        $markdown = "Before\n\n{{ cli }}\nSome line\n\n* Item 1\n* Item 2\n{{ cli }}\n\nAfter\n";

        $document = MarkdownFactory::parser(CurrentContext::cli())->parse($markdown);

        $blocks = $document->children();
        static::assertCount(3, $blocks);
        static::assertInstanceOf(Paragraph::class, $blocks[0]);
        static::assertInstanceOf(ContextSpecificBlock::class, $blocks[1]);
        static::assertSame('cli', $blocks[1]->getType());
        static::assertCount(2, $blocks[1]->children());
        static::assertInstanceOf(Paragraph::class, $blocks[1]->children()[0]);
        static::assertInstanceOf(Paragraph::class, $blocks[2]);
        static::assertInstanceOf(Document::class, $blocks[1]->parent());
    }
}
