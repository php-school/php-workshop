<?php

namespace PhpSchool\PhpWorkshopTest;

use PhpSchool\PhpWorkshop\WorkshopType;
use PHPUnit\Framework\TestCase;

/**
 * Class TestWorkshopType
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class TestWorkshopType extends TestCase
{
    public function testIsTutorialMode() : void
    {
        $tutorial = WorkshopType::TUTORIAL();
        $standard = WorkshopType::STANDARD();

        static::assertTrue($tutorial->isTutorialMode());
        static::assertFalse($standard->isTutorialMode());
    }
}
