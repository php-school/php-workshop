<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use Countable;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testException(): void
    {
        $e = new InvalidArgumentException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testExceptionFromTypeMisMatchConstructor(): void
    {
        $e = InvalidArgumentException::typeMisMatch('string', new \stdClass());
        $this->assertEquals('Expected: "string" Received: "stdClass"', $e->getMessage());
    }

    public function testExceptionFromNotValidParameterConstructor(): void
    {
        $e = InvalidArgumentException::notValidParameter('number', [1, 2], 3);
        $this->assertEquals('Parameter: "number" can only be one of: "1", "2" Received: "3"', $e->getMessage());
    }

    public function testExceptionFromMissingImplements(): void
    {
        $e = InvalidArgumentException::missingImplements(new \stdClass(), Countable::class);
        self::assertEquals('"stdClass" is required to implement "Countable", but it does not', $e->getMessage());
    }

    public function testExceptionFromNotInArray(): void
    {
        $e = InvalidArgumentException::notInArray('not-a-type', ['type-a', 'type-b']);
        self::assertEquals('Value "not-a-type" is not an element of the valid values: type-a, type-b', $e->getMessage());
    }

    /**
     * @dataProvider stringifyProvider
     */
    public function testStringify($value, string $expected): void
    {
        $rM = new \ReflectionMethod(InvalidArgumentException::class, 'stringify');
        $rM->setAccessible(true);

        $this->assertEquals($rM->invoke(null, $value), $expected);
    }

    public function stringifyProvider(): array
    {
        return [
            [new \stdClass(), 'stdClass'],
            [[1, 2, 3], '1", "2", "3'],
            [1, '1'],
            ['1', '1'],
            [true, 'true'],
        ];
    }
}
