<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands\Cloud;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cloud\Run;
use PHPUnit\Framework\TestCase;

class RunTest extends TestCase
{
    public function testShorthand(): void
    {
        $shorthand = new Run();

        $result = $shorthand->__invoke([]);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('Click the Run button in the bottom right', $result[0]->getContent());
    }
}
