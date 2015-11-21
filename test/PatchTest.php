<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CodeModification;
use PhpSchool\PhpWorkshop\Patch;
use PHPUnit_Framework_TestCase;

/**
 * Class PatchTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PatchTest extends PHPUnit_Framework_TestCase
{
    public function testWithModification()
    {
        $patch = new Patch;
        $modification = new CodeModification(CodeModification::TYPE_BEFORE, 'MEH');
        $new = $patch->withModification($modification);
        
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifications());
        $this->assertEquals([$modification], $new->getModifications());
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
