<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CodeInsertion;
use PhpSchool\PhpWorkshop\Patch;
use PHPUnit_Framework_TestCase;

/**
 * Class PatchTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PatchTest extends PHPUnit_Framework_TestCase
{
    public function testWithInsertion()
    {
        $patch = new Patch;
        $insertion = new CodeInsertion(CodeInsertion::TYPE_BEFORE, 'MEH');
        $new = $patch->withInsertion($insertion);
        
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getInsertions());
        $this->assertEquals([$insertion], $new->getInsertions());
    }

    public function testWithTransformer()
    {
        $patch = new Patch;
        $transformer = function (array $statements) {
            return $statements;
        };
        $new = $patch->withTransformer($transformer);
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getTransformers());
        $this->assertEquals([$transformer], $new->getTransformers());
    }
}
