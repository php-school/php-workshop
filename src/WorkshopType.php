<?php

namespace PhpSchool\PhpWorkshop;

use MyCLabs\Enum\Enum;

/**
 * @method static WorkshopType STANDARD()
 * @method static WorkshopType TUTORIAL()
 *
 * @extends Enum<int>
 */
class WorkshopType extends Enum
{
    public const STANDARD = 1;
    public const TUTORIAL = 2;

    /**
     * @return bool
     */
    public function isTutorialMode(): bool
    {
        return $this->getValue() === static::TUTORIAL;
    }
}
