<?php

namespace PhpSchool\PhpWorkshopTest\Markdown;

use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PhpSchool\PhpWorkshop\Markdown\CurrentContext;
use PHPUnit\Framework\TestCase;

class CurrentContextTest extends TestCase
{
    public function testExceptionIsThrownIfConstructedWithInvalidType(): void
    {
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionMessage('Parameter: "context" can only be one of: "cli", "cloud" Received: "invalid"');

        new CurrentContext('invalid');
    }

    public function testContext(): void
    {
        $context = new CurrentContext('cloud');
        static::assertEquals('cloud', $context->get());

        $context = new CurrentContext('cli');
        static::assertEquals('cli', $context->get());

        $context = CurrentContext::cli();
        static::assertEquals('cli', $context->get());

        $context = CurrentContext::cloud();
        static::assertEquals('cloud', $context->get());
    }
}
