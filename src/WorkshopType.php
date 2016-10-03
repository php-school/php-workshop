<?php

namespace PhpSchool\PhpWorkshop;

use MyCLabs\Enum\Enum;

/**
 * @method static WorkshopType STANDARD()
 * @method static WorkshopType TUTORIAL()
 * @author Michael Woodward <mikeymike.mw@gmail.com>
 */
class WorkshopType extends Enum
{
    const STANDARD = 1;
    const TUTORIAL = 2;
}
