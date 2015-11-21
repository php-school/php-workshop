<?php

namespace PhpSchool\PhpWorkshopTest;

use Assert\InvalidArgumentException;
use PhpSchool\PhpWorkshop\CodeModification;
use PHPUnit_Framework_TestCase;

/**
 * Class CodeModificationTest
 * @package PhpSchool\PhpWorkshopTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CodeModificationTest extends PHPUnit_Framework_TestCase
{
    public function testInvalidType()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new CodeModification('notatype', '');
    }

    public function testInvalidCode()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        new CodeModification(CodeModification::TYPE_BEFORE, new \stdClass);
    }

    public function testGetters()
    {
        $mod = new CodeModification(CodeModification::TYPE_BEFORE, '<?php codez');
        $this->assertEquals(CodeModification::TYPE_BEFORE, $mod->getType());
        $this->assertEquals('<?php codez', $mod->getCode());
    }
}
