<?php

namespace PhpSchool\PhpWorkshopTest\Asset;

use PhpSchool\PhpWorkshop\Exercise\TemporaryDirectoryTrait;

class TemporaryDirectoryTraitImpl
{
    use TemporaryDirectoryTrait {
        getTemporaryPath as public;
    }
}
