<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Parser;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Cursor;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\InlineParserContext;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Parser\HandleBarParser;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Context;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;
use PHPUnit\Framework\TestCase;

class HandleBarParserTest extends TestCase
{
    public function testGetCharacters(): void
    {
        $parser = new HandleBarParser([]);
        static::assertEquals(['{'], $parser->getCharacters());
    }

    /**
     * @dataProvider noShorthandsProvider
     */
    public function testParseWithNoShortHands(string $content): void
    {
        $cursor  = new Cursor($content);
        $context = $this->createMock(InlineParserContext::class);
        $context->expects($this->any())
            ->method('getCursor')
            ->willReturn($cursor);

        $context->expects($this->never())->method('getContainer');

        $parser = new HandleBarParser([]);

        static::assertFalse($parser->parse($context));
    }

    public function noShorthandsProvider(): array
    {
        return [
            ['some content'],
            ['{{cloud}}'],
            ['{{cloud wut}}'],
            ['{{ cloud wut}}'],
            ['{{ cloud wut }}'],
            ['{{ cloud wut }}'],
        ];
    }

    /**
     * @dataProvider parseProvider
     */
    public function testWithShorthand(string $content, bool $expectedParseResult, array $expectedArgs): void
    {
        $cursor  = new Cursor($content);
        $context = $this->createMock(InlineParserContext::class);
        $context->expects($this->any())
            ->method('getCursor')
            ->willReturn($cursor);

        if ($expectedParseResult) {
            $container = static::getMockForAbstractClass(
                AbstractBlock::class,
                [],
                '',
                true,
                true,
                true,
                ['appendChild'],
            );
            $container->expects($this->once())
                ->method('appendChild')
                ->with(static::callback(function (Text $text) {
                    static::assertEquals('Some element', $text->getContent());
                    return true;
                }));

            $context->expects($this->once())->method('getContainer')->willReturn($container);
        } else {
            $context->expects($this->never())->method('getContainer');
        }

        $shorthand = new class () implements ShorthandInterface {
            public $args = [];
            public function __invoke(array $callArgs): array
            {
                $this->args = $callArgs;

                return [new Text('Some element')];
            }

            public function getCode(): string
            {
                return 'test';
            }
        };
        $parser = new HandleBarParser([$shorthand]);

        static::assertEquals($expectedParseResult, $parser->parse($context));
        static::assertEquals($expectedArgs, $shorthand->args);
    }

    public function parseProvider(): array
    {
        return [
            ['some content', false, []],
            ['{{test}}', true, []],
            ['{{test arg1}}', true, ['arg1']],
            ['{{ test arg1}}', true, ['arg1']],
            ['{{   test  arg1}}', true, ['arg1']],
            ['{{   test  arg1  }}', true, ['arg1']],
            ['{{   test  arg1  arg2}}', true, ['arg1', 'arg2']],
            ['{{   test  arg1  arg2}}', true, ['arg1', 'arg2']],
            ['{{ test "an argument in double quotes" }}', true, ['an argument in double quotes']],
            ['{{ test \'an argument in single quotes\' }}', true, ['an argument in single quotes']],
            ['{{ test "an argument" "second arg"}}', true, ['an argument', 'second arg']],
            ['{{ test   "an argument"   "second arg" }}', true, ['an argument', 'second arg']],
            ['{{ test   "an argument"   \'second arg\' }}', true, ['an argument', 'second arg']],
            ['{{ test   "argument1"   \'second arg\' }}', true, ['argument1', 'second arg']],
            ['{{ test   "argument1"   \'argument2\' }}', true, ['argument1', 'argument2']],
            ['{{ test   "argument1"   \'argument 2\' }}', true, ['argument1', 'argument 2']],
            ['{{ test   "argument 1"   \'argument2\' }}', true, ['argument 1', 'argument2']],
            ['{{ test   "argument 1"   \'argument2\' arg3}}', true, ['argument 1', 'argument2', 'arg3']],
            [
                '{{ test   "argument 1"   \'a really long argument two\' arg3}}',
                true,
                ['argument 1', 'a really long argument two', 'arg3'],
            ],
        ];
    }

    public function testParsingWithContextShorthand(): void
    {
        $cursor  = new Cursor('{{ context cli  \'CLI ONLY CONTENT\'}}');
        $context = $this->createMock(InlineParserContext::class);
        $context->expects($this->any())
            ->method('getCursor')
            ->willReturn($cursor);

        $container = static::getMockForAbstractClass(
            AbstractBlock::class,
            [],
            '',
            true,
            true,
            true,
            ['appendChild'],
        );
        $container->expects($this->once())
            ->method('appendChild')
            ->with(static::callback(function (Text $text) {
                static::assertEquals('CLI ONLY CONTENT', $text->getContent());
                return true;
            }));

        $context->expects($this->once())->method('getContainer')->willReturn($container);

        $parser = new HandleBarParser([new Context(CurrentContext::cli())]);
        static::assertTrue($parser->parse($context));
    }
}
