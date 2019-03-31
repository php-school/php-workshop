<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\CodeInsertion;
use PhpSchool\PhpWorkshop\Patch;
use PHPUnit\Framework\TestCase;

/**
 * Class PatchTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class PatchTest extends TestCase
{
    public function testWithInsertion() : void
    {
        $patch = new Patch;
        $insertion = new CodeInsertion(CodeInsertion::TYPE_BEFORE, 'MEH');
        $new = $patch->withInsertion($insertion);
        
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$insertion], $new->getModifiers());
    }

    public function testWithTransformer() : void
    {
        $patch = new Patch;
        $transformer = function (array $statements) {
            return $statements;
        };
        $new = $patch->withTransformer($transformer);
        $this->assertNotSame($patch, $new);
        $this->assertEmpty($patch->getModifiers());
        $this->assertEquals([$transformer], $new->getModifiers());
    }
}
