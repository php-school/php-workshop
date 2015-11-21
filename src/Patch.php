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
     * @var CodeModification[]
     */
    private $modifications = [];

    /**
     * @var array
     */
    private $transformers = [];

    /**
     * @param CodeModification $modification
     * @return static
     */
    public function withModification(CodeModification $modification)
    {
        $new = clone $this;
        $new->modifications[] = $modification;
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
    public function getModifications()
    {
        return $this->modifications;
    }

    /**
     * @return array
     */
    public function getTransformers()
    {
        return $this->transformers;
    }
}
