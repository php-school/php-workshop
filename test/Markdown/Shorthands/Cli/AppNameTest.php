<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands\Cli;

use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Node\Inline\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\AppName;
use PHPUnit\Framework\TestCase;

class AppNameTest extends TestCase
{
    public function testShorthand(): void
    {
        $short = new AppName('learnyouphp');

        $result = $short->__invoke([]);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('learnyouphp', $result[0]->getContent());
    }

    public function testShorthandCode(): void
    {
        $short = new AppName('learnyouphp');

        $result = $short->__invoke(['`']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Code::class, $result[0]);
        self::assertEquals('learnyouphp', $result[0]->getContent());
    }

    public function testShorthandBold(): void
    {
        $short = new AppName('learnyouphp');

        $result = $short->__invoke(['*']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Strong::class, $result[0]);
        self::assertCount(1, $result[0]->children());
        self::assertInstanceOf(Text::class, $result[0]->children()[0]);
        self::assertEquals('learnyouphp', $result[0]->children()[0]->getContent());
    }

    public function testShorthandItalic(): void
    {
        $short = new AppName('learnyouphp');

        $result = $short->__invoke(['_']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Emphasis::class, $result[0]);
        self::assertCount(1, $result[0]->children());
        self::assertInstanceOf(Text::class, $result[0]->children()[0]);
        self::assertEquals('learnyouphp', $result[0]->children()[0]->getContent());
    }
}
