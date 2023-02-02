<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Parser;

use League\CommonMark\ContextInterface;
use League\CommonMark\Cursor;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockParser;
use PHPUnit\Framework\TestCase;

class ContextSpecificBlockParserTest extends TestCase
{
    public function testGetRegex(): void
    {
        static::assertEquals('/^{{\s?(cli|cloud)\s?}}/', ContextSpecificBlockParser::getParserRegex());
    }

    public function testExceptionIsThrownIfConstructedWithInvalidType(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Parameter: "type" can only be one of: "cli", "cloud" Received: "invalid"');

        static::assertEquals('/^{{\s?(cli)\s?}}/', ContextSpecificBlockParser::getEndBlockRegex('invalid'));
    }

    public function testGetEndRegex(): void
    {
        static::assertEquals('/^{{\s?(cli)\s?}}/', ContextSpecificBlockParser::getEndBlockRegex('cli'));
    }

    public function testParseReturnsFalseWhenIncorrectCharacter(): void
    {
        $block = new ContextSpecificBlockParser();

        $context = $this->createMock(ContextInterface::class);

        static::assertFalse($block->parse($context, new Cursor('some text')));
    }

    public function testParseReturnsFalseWhenNotMatchingRegex(): void
    {
        $block = new ContextSpecificBlockParser();

        $context = $this->createMock(ContextInterface::class);

        static::assertFalse($block->parse($context, new Cursor('{ something else')));
    }

    public function testParseAddsBlockWithCorrectType(): void
    {
        $block = new ContextSpecificBlockParser();

        $context = $this->createMock(ContextInterface::class);
        $context->expects($this->once())
            ->method('addBlock')
            ->with(static::callback(function (ContextSpecificBlock $block) {
                static::assertEquals('cloud', $block->getType());
                return true;
            }));

        static::assertTrue($block->parse($context, new Cursor('{{ cloud }}')));
    }
}
