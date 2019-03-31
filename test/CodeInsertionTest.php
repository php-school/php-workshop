<?php

namespace PhpSchool\PhpWorkshopTest;

use Assert\InvalidArgumentException;
use PhpSchool\PhpWorkshop\CodeInsertion;
use PHPUnit\Framework\TestCase;

/**
 * Class CodeInsertionTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeInsertionTest extends TestCase
{
    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        new CodeInsertion('notatype', '');
    }

    public function testInvalidCode()
    {
        $this->expectException(InvalidArgumentException::class);
        new CodeInsertion(CodeInsertion::TYPE_BEFORE, new \stdClass);
    }

    public function testGetters()
    {
        $mod = new CodeInsertion(CodeInsertion::TYPE_BEFORE, '<?php codez');
        $this->assertEquals(CodeInsertion::TYPE_BEFORE, $mod->getType());
        $this->assertEquals('<?php codez', $mod->getCode());
    }
}
