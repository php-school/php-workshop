<?php

namespace PhpSchool\PhpWorkshop\Patch;

use PhpParser\Node\Stmt;

interface Transformer
{
    /**
     * @param array<Stmt> $statements
     * @return array<Stmt>
     */
    public function transform(array $statements): array;
}
