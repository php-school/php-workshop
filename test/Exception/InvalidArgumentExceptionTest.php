<?php

namespace PhpSchool\PhpWorkshopTest\Exception;

use Countable;
use PhpSchool\PhpWorkshop\Exception\InvalidArgumentException;
use PHPUnit_Framework_TestCase;

/**
 * Class InvalidArgumentExceptionTest
 * @package PhpSchool\PhpWorkshopTest\Exception
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InvalidArgumentExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $e = new InvalidArgumentException('nope');
        $this->assertEquals('nope', $e->getMessage());
    }

    public function testExceptionFromTypeMisMatchConstructor()
    {
        $e = InvalidArgumentException::typeMisMatch('string', new \stdClass);
        $this->assertEquals('Expected: "string" Received: "stdClass"', $e->getMessage());
    }

    public function testExceptionFromNotValidParameterConstructor()
    {
        $e = InvalidArgumentException::notValidParameter('number', [1, 2], 3);
        $this->assertEquals('Parameter: "number" can only be one of: "1", "2" Received: "3"', $e->getMessage());
    }

    public function testExceptionFromMissingImplements()
    {
        $e = InvalidArgumentException::missingImplements(new \stdClass, Countable::class);
        self::assertEquals('"stdClass" is required to implement "Countable", but it does not', $e->getMessage());
    }

    /**
     * @dataProvider stringifyProvider
     * @param mixed $value
     * @param string $expected
     */
    public function testStringify($value, $expected)
    {
        $rM = new \ReflectionMethod(InvalidArgumentException::class, 'stringify');
        $rM->setAccessible(true);

        $this->assertEquals($rM->invoke(null, $value), $expected);
    }

    /**
     * @return array
     */
    public function stringifyProvider()
    {
        return [
            [new \stdClass, 'stdClass'],
            [[1, 2, 3], '1", "2", "3'],
            [1, "1"],
            ["1", "1"],
            [true, "true"],
        ];
    }
}
