<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop;

use Closure;
use PhpSchool\PhpWorkshop\Patch\Transformer;

/**
 * This class is responsible for storing the modifications that should
 * be made to a PHP file. That includes insertions and transformers.
 * A transformer is a simple closure which should receives an AST
 * representation of the student's solution and it should return the modified AST.
 *
 * Transformers can also be class implementing `Transformer`.
 *
 * An insertion is a block of code that can be inserted at the top or bottom of
 * the students solution.
 */
final class Patch
{
    /**
     * @var array<CodeInsertion|Closure|Transformer>
     */
    private $modifications = [];

    /**
     * Add a new `CodeInsertion`. `Patch` is immutable so a new instance is returned.
     *
     * @param CodeInsertion $insertion
     * @return self
     */
    public function withInsertion(CodeInsertion $insertion): self
    {
        $new = clone $this;
        $new->modifications[] = $insertion;
        return $new;
    }

    /**
     * Add a new transformer (`Closure` OR `Transformer`). `Patch` is immutable so a new instance is returned.
     *
     * @param Closure|Transformer $closure
     * @return self
     */
    public function withTransformer($closure): self
    {
        $new = clone $this;
        $new->modifications[] = $closure;
        return $new;
    }

    /**
     * Retrieve all the modifications including insertions (`CodeInsertion`'s)
     * & transformers (`Closure`'s OR `Transformer`'s)
     *
     * @return array<CodeInsertion|Closure|Transformer>
     */
    public function getModifiers(): array
    {
        return $this->modifications;
    }
}
