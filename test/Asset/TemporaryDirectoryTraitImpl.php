<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;

/**
 * Class TemporaryDirectoryTraitImpl
 * @package PhpSchool\PhpWorkshopTest\Asset
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class TemporaryDirectoryTraitImpl
{
    use TemporaryDirectoryTrait { getTemporaryPath as public;
    }
}
