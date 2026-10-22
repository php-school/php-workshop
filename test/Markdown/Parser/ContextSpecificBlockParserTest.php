<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Parser;

use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Parser\Block\BlockContinue;
use League\CommonMark\Parser\Block\BlockContinueParserInterface;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\MarkdownParserStateInterface;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\Block\ContextSpecificBlock;
use PhpSchool\PhpWorkshop\Markdown\Parser\ContextSpecificBlockContinueParser;
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

    public function testTryStartReturnsNoneWhenIncorrectCharacter(): void
    {
        $parser = new ContextSpecificBlockParser();

        static::assertNull($parser->tryStart(
            new Cursor('some text'),
            $this->createMock(MarkdownParserStateInterface::class),
        ));
    }

    public function testTryStartReturnsNoneWhenNotMatchingRegex(): void
    {
        $parser = new ContextSpecificBlockParser();

        static::assertNull($parser->tryStart(
            new Cursor('{ something else'),
            $this->createMock(MarkdownParserStateInterface::class),
        ));
        static::assertNull($parser->tryStart(
            new Cursor('{{ unknown }}'),
            $this->createMock(MarkdownParserStateInterface::class),
        ));
    }

    public function testTryStartCreatesBlockWithCorrectType(): void
    {
        $parser = new ContextSpecificBlockParser();

        $start = $parser->tryStart(
            new Cursor('{{ cloud }}'),
            $this->createMock(MarkdownParserStateInterface::class),
        );

        static::assertNotNull($start);
        $parsers = [...$start->getBlockParsers()];
        static::assertCount(1, $parsers);
        static::assertInstanceOf(ContextSpecificBlockContinueParser::class, $parsers[0]);
        static::assertInstanceOf(ContextSpecificBlock::class, $parsers[0]->getBlock());
        static::assertSame('cloud', $parsers[0]->getBlock()->getType());
    }

    public function testContinueParserIsAContainerAcceptingAnyBlock(): void
    {
        $parser = new ContextSpecificBlockContinueParser('cli');

        static::assertTrue($parser->isContainer());
        static::assertTrue($parser->canContain($this->createMock(AbstractBlock::class)));
    }

    public function testTryContinueContinuesOnContentLines(): void
    {
        $parser = new ContextSpecificBlockContinueParser('cli');
        $active = $this->createMock(BlockContinueParserInterface::class);

        foreach (['Some line', '* Item 1', '* Item 2', '{{ cloud }}'] as $line) {
            $result = $parser->tryContinue(new Cursor($line), $active);
            static::assertNotNull($result);
            static::assertFalse($result->isFinalize(), $line);
        }
    }

    public function testTryContinueFinishesOnEndTag(): void
    {
        $parser = new ContextSpecificBlockContinueParser('cli');
        $active = $this->createMock(BlockContinueParserInterface::class);

        foreach (['{{ cli }}', '{{cli}}', '  {{ cli }}'] as $line) {
            $result = $parser->tryContinue(new Cursor($line), $active);
            static::assertInstanceOf(BlockContinue::class, $result);
            static::assertTrue($result->isFinalize(), $line);
        }
    }
}
