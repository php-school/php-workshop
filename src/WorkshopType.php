<?php

namespace PhpSchool\PhpWorkshop;

use MyCLabs\Enum\Enum;

/**
 * @method static WorkshopType STANDARD()
 * @method static WorkshopType TUTORIAL()
 */
class WorkshopType extends Enum
{
    public const STANDARD = 1;
    public const TUTORIAL = 2;

    /**
     * @return bool
     */
    public function isTutorialMode()
    {
        return $this->getValue() === static::TUTORIAL;
    }
}
