<?php

namespace PhpSchool\PhpWorkshop\Patch;

use PhpParser\Node\Stmt;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\TryCatch;

class WrapInTryCatch implements Transformer
{
    /**
     * @var string
     */
    private $exceptionClass;

    /**
     * @var array<Stmt>
     */
    private $statements;

    /**
     * @param string $exceptionClass
     * @param array<Stmt>|null $statements
     */
    public function __construct(string $exceptionClass = \Exception::class, array $statements = null)
    {
        $this->exceptionClass = $exceptionClass;
        $this->statements = $statements ?: [
            new Echo_([
                new MethodCall(new Variable('e'), 'getMessage'),
            ]),
        ];
    }

    /**
     * @param array<Stmt> $statements
     * @return array<Stmt>
     */
    public function transform(array $statements): array
    {
        return [
            new TryCatch(
                $statements,
                [
                    new Catch_(
                        [new Name($this->exceptionClass)],
                        new Variable('e'),
                        $this->statements,
                    ),
                ],
            ),
        ];
    }
}
