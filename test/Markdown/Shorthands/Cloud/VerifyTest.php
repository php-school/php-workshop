<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands\Cloud;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Cloud\Verify;
use PHPUnit\Framework\TestCase;

class VerifyTest extends TestCase
{
    public function testShorthand(): void
    {
        $shorthand = new Verify();

        $result = $shorthand->__invoke([]);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('Click the Verify button in the bottom right', $result[0]->getContent());
    }
}
