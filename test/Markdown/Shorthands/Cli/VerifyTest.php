<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands\Cli;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Exception\RuntimeException;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cli\Verify;
use PHPUnit\Framework\TestCase;

class VerifyTest extends TestCase
{
    public function testExceptionIsThrownIfNoSolutionFile(): void
    {
        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('The solution file must be specified');

        $shorthand = new Verify('learnyouphp');

        $shorthand->__invoke([]);
    }

    public function testShorthand(): void
    {
        $shorthand = new Verify('learnyouphp');

        $result = $shorthand->__invoke(['solution.php']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('learnyouphp verify solution.php', $result[0]->getContent());
    }
}
