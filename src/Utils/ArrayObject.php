<?php

namespace PhpSchool\PhpWorkshop\Utils;

/**
 * Class ArrayObject
 * @package PhpSchool\PhpWorkshop\Utils
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ArrayObject extends \ArrayObject
{

    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static (array_map($callback, $this->getArrayCopy()));
    }

    /**
     * @param string $glue
     * @return string
     */
    public function implode($glue)
    {
        return implode($glue, $this->getArrayCopy());
    }
}
