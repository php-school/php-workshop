<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Parser;

use League\CommonMark\Parser\MarkdownParser;
use PhpSchool\PhpWorkshop\Factory\MarkdownEnvironmentFactory;
use PhpSchool\PhpWorkshop\Markdown\Renderer\ContextSpecificRenderer;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Parser\Cursor;
use League\CommonMark\Parser\InlineParserContext;
use League\CommonMark\Reference\ReferenceMap;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Parser\HandleBarParser;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Context;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\ShorthandInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class HandleBarParserTest extends TestCase
{
    public function testMatchDefinition(): void
    {
        $parser = new HandleBarParser([]);
        $regex = $parser->getMatchDefinition()->getRegex();

        static::assertSame(1, preg_match($regex, 'before {{ test arg }} after', $matches));
        static::assertSame('{{ test arg }}', $matches[0]);
        static::assertSame(0, preg_match($regex, 'some content'));
        static::assertSame(0, preg_match($regex, '{ test }'));
    }

    /**
     * Runs the parser the way commonmark does: find the match, then hand the parser a context for it.
     *
     * @return array{0: bool, 1: Paragraph, 2: Cursor}
     */
    private function parse(HandleBarParser $parser, string $content): array
    {
        $paragraph = new Paragraph();
        $cursor = new Cursor($content);
        $context = new InlineParserContext($cursor, $paragraph, new ReferenceMap());

        static::assertSame(1, preg_match($parser->getMatchDefinition()->getRegex(), $content, $matches));

        return [$parser->parse($context->withMatches([$matches[0]])), $paragraph, $cursor];
    }

    #[DataProvider('noShorthandsProvider')]
    public function testParseWithNoShortHands(string $content): void
    {
        if ($content === 'some content') {
            static::assertSame(0, preg_match((new HandleBarParser([]))->getMatchDefinition()->getRegex(), $content));
            return;
        }

        [$result, $paragraph, $cursor] = $this->parse(new HandleBarParser([]), $content);

        static::assertFalse($result);
        static::assertCount(0, $paragraph->children());
        static::assertSame(0, $cursor->getPosition());
    }

    public static function noShorthandsProvider(): array
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

    #[DataProvider('parseProvider')]
    public function testWithShorthand(string $content, bool $expectedParseResult, array $expectedArgs): void
    {
        $shorthand = new class implements ShorthandInterface {
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

        if (!$expectedParseResult) {
            static::assertSame(0, preg_match($parser->getMatchDefinition()->getRegex(), $content));
            static::assertSame([], $shorthand->args);
            return;
        }

        [$result, $paragraph, $cursor] = $this->parse($parser, $content);

        static::assertTrue($result);
        static::assertEquals($expectedArgs, $shorthand->args);
        static::assertCount(1, $paragraph->children());
        static::assertInstanceOf(Text::class, $paragraph->firstChild());
        static::assertSame('Some element', $paragraph->firstChild()->getLiteral());
        static::assertSame(mb_strlen($content), $cursor->getPosition());
    }

    public static function parseProvider(): array
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
        $parser = new HandleBarParser([new Context(CurrentContext::cli())]);

        [$result, $paragraph] = $this->parse($parser, '{{ context cli  \'CLI ONLY CONTENT\'}}');

        static::assertTrue($result);
        static::assertCount(1, $paragraph->children());
        static::assertInstanceOf(Text::class, $paragraph->firstChild());
        static::assertSame('CLI ONLY CONTENT', $paragraph->firstChild()->getLiteral());
    }

    public function testShorthandsAreReplacedInParsedMarkdown(): void
    {
        $parser = new MarkdownParser((new MarkdownEnvironmentFactory())->create(80, new ContextSpecificRenderer(CurrentContext::cli()), [new Context(CurrentContext::cli())]));

        $document = $parser->parse("Hello {{ context cli 'CLI ONLY' }} and {{ context cloud 'CLOUD ONLY' }} {{ unknown }}");
        $paragraph = $document->firstChild();

        $text = '';
        foreach ($paragraph->children() as $child) {
            $text .= $child->getLiteral();
        }

        static::assertSame('Hello CLI ONLY and  {{ unknown }}', $text);
    }
}
