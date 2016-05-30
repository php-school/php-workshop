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
     * @var array
     */
    private $modifications = [];

    /**
     * @param CodeInsertion $insertion
     * @return static
     */
    public function withInsertion(CodeInsertion $insertion)
    {
        $new = clone $this;
        $new->modifications[] = $insertion;
        return $new;
    }

    /**
     * @param Closure $closure
     * @return Patch
     */
    public function withTransformer(Closure $closure)
    {
        $new = clone $this;
        $new->modifications[] = $closure;
        return $new;
    }

    /**
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifications;
    }
}
