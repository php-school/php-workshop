<?php

namespace PhpSchool\PhpWorkshop\Patch;

use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\DeclareDeclare;

class ForceStrictTypes implements Transformer
{
    /**
     * @param array<Stmt> $statements
     * @return array<Stmt>
     */
    public function transform(array $statements): array
    {
        if ($this->isFirstStatementStrictTypesDeclare($statements)) {
            return $statements;
        }

        $declare = new \PhpParser\Node\Stmt\Declare_([
            new DeclareDeclare(
                new \PhpParser\Node\Identifier('strict_types'),
                new LNumber(1)
            )
        ]);

        return array_merge([$declare], $statements);
    }

    /**
     * @param array<Stmt> $statements
     */
    public function isFirstStatementStrictTypesDeclare(array $statements): bool
    {
        return isset($statements[0]) && $statements[0] instanceof Declare_;
    }
}
