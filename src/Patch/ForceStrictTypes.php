<?php

namespace PhpSchool\PhpWorkshop\Patch;

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;

class ForceStrictTypes implements Transformer
{
    public function transform(array $ast): array
    {
        if ($this->isFirstStatementStrictTypesDeclare($ast)) {
            return $ast;
        }

        $declare = new \PhpParser\Node\Stmt\Declare_([
            new DeclareDeclare(
                new \PhpParser\Node\Identifier('strict_types'),
                new LNumber(1)
            )
        ]);

        return array_merge([$declare], $ast);
    }

    public function isFirstStatementStrictTypesDeclare(array $statements): bool
    {
        return isset($statements[0]) && $statements[0] instanceof Declare_;
    }
}