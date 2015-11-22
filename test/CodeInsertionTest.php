<?php

namespace PhpSchool\PhpWorkshopTest;

use Assert\InvalidArgumentException;
use PhpSchool\PhpWorkshop\CodeInsertion;
use PHPUnit_Framework_TestCase;

/**
 * Class CodeInsertionTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeInsertionTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new CodeInsertion('notatype', '');
    }

    public function testInvalidCode()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new CodeInsertion(CodeInsertion::TYPE_BEFORE, new \stdClass);
    }

    public function testGetters()
    {
        $mod = new CodeInsertion(CodeInsertion::TYPE_BEFORE, '<?php codez');
        $this->assertEquals(CodeInsertion::TYPE_BEFORE, $mod->getType());
        $this->assertEquals('<?php codez', $mod->getCode());
    }
}
