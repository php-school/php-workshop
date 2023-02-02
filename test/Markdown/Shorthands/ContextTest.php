<?php

namespace PhpSchool\PhpWorkshopTest\Markdown\Shorthands;

use League\CommonMark\Inline\Element\Text;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PhpSchool\PhpWorkshop\Markdown\Shorthands\Context;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testIgnoresContextsNotMatchingTheCurrentContext(): void
    {
        $currentContext = CurrentContext::cli();

        $context = new Context($currentContext);

        self::assertEquals([], $context->__invoke([]));
    }

    public function testIgnoresCallsWithNoContent(): void
    {
        $currentContext = CurrentContext::cli();

        $context = new Context($currentContext);

        self::assertEquals([], $context->__invoke(['cli']));
    }

    public function testWithContent(): void
    {
        $currentContext = CurrentContext::cli();

        $context = new Context($currentContext);

        $result = $context->__invoke(['cli', 'some content']);
        self::assertCount(1, $result);
        self::assertInstanceOf(Text::class, $result[0]);
        self::assertEquals('some content', $result[0]->getContent());
    }
}
