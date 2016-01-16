<?php

namespace PhpSchool\PhpWorkshopTest\Util;

use PhpSchool\PhpWorkshop\Utils\ArrayObject;
use PHPUnit_Framework_TestCase;

/**
 * Class ArrayObjectTest
 * @package PhpSchool\PhpWorkshopTest\Util
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ArrayObjectTest extends PHPUnit_Framework_TestCase
{
    public function testMap()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $new = $arrayObject->map(function ($elem) {
            return $elem * 2;
        });

        $this->assertNotSame($arrayObject, $new);
        $this->assertEquals([2, 4, 6], $new->getArrayCopy());
    }

    public function testImplode()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $this->assertSame('1 2 3', $arrayObject->implode(' '));
    }

    public function testPrepend()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $new = $arrayObject->prepend(0);

        $this->assertNotSame($new, $arrayObject);
        $this->assertSame([0, 1, 2, 3], $new->getArrayCopy());
    }

    public function testAppend()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $new = $arrayObject->append(4);

        $this->assertNotSame($new, $arrayObject);
        $this->assertSame([1, 2, 3, 4], $new->getArrayCopy());
    }

    public function testGetIterator()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $this->assertSame([1, 2, 3], iterator_to_array($arrayObject));
    }

    public function testGetArrayCopy()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $this->assertSame([1, 2, 3], $arrayObject->getArrayCopy());
    }

    public function testGetArrayCopyWithVariadicConstruction()
    {
        $arrayObject = new ArrayObject(1, 2, 3);
        $this->assertSame([1, 2, 3], $arrayObject->getArrayCopy());
    }
}
