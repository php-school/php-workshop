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

    public function testImple()
    {
        $arrayObject = new ArrayObject([1, 2, 3]);
        $this->assertSame('1 2 3', $arrayObject->implode(' '));
    }
}
