<?php

namespace PhpSchool\PhpWorkshop;

use Closure;

/**
 * Class Patch
 * @package PhpSchool\PhpWorkshop
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class Patch
{
    /**
     * @var CodeInsertion[]
     */
    private $insertions = [];

    /**
     * @var array
     */
    private $transformers = [];

    /**
     * @param CodeInsertion $insertion
     * @return static
     */
    public function withInsertion(CodeInsertion $insertion)
    {
        $new = clone $this;
        $new->insertions[] = $insertion;
        return $new;
    }

    /**
     * @param Closure $closure
     * @return Patch
     */
    public function withTransformer(Closure $closure)
    {
        $new = clone $this;
        $new->transformers[] = $closure;
        return $new;
    }

    /**
     * @return array
     */
    public function getInsertions()
    {
        return $this->insertions;
    }

    /**
     * @return array
     */
    public function getTransformers()
    {
        return $this->transformers;
    }
}
