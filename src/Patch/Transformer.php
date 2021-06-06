<?php

namespace PhpSchool\PhpWorkshop\Patch;

interface Transformer
{
    public function transform(array $ast): array;
}
