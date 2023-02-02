<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands\Cli;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\Run;
use PHPUnit\Framework\TestCase;

class RunTest extends TestCase
{
    public function testExceptionIsThrownIfNoSolutionFile(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('The solution file must be specified');

        $context = new Run('learnyouphp');

        $context->__invoke([]);
    }

    public function testShorthand(): void
    {
        $context = new Run('learnyouphp');

        $result = $context->__invoke(['solution.php']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('learnyouphp run solution.php', $result[0]->getContent());
    }
}
