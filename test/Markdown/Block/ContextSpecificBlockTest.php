<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Block;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
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

    public function testConfig(): void
    {
        $block = new ContextSpecificBlock('cli');

        static::assertTrue($block->canContain($this->getMockForAbstractClass(AbstractBlock::class)));
        static::assertFalse($block->isCode());
    }

    public function testMatchesNextLine(): void
    {
        $block = new ContextSpecificBlock('cli');

        static::assertTrue($block->matchesNextLine(new Cursor('Some line')));
        static::assertTrue($block->matchesNextLine(new Cursor('* Item 1')));
        static::assertTrue($block->matchesNextLine(new Cursor('* Item 2')));

        static::assertFalse($block->matchesNextLine(new Cursor('{{ cli }}')));
    }
}
