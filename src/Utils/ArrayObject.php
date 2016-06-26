<?php

namespace PhpSchool\PhpWorkshop\Utils;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Class ArrayObject
 * @package PhpSchool\PhpWorkshop\Utils
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class ArrayObject implements IteratorAggregate, Countable
{

    /**
     * @var array
     */
    private $array;

    /**
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static (array_map($callback, $this->array));
    }

    /**
     * @param string $glue
     * @return string
     */
    public function implode($glue)
    {
        return implode($glue, $this->array);
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function prepend($value)
    {
        return new static(array_merge([$value], $this->array));
    }

    /**
     * @param mixed $value
     * @return static
     */
    public function append($value)
    {
        return new static(array_merge($this->array, [$value]));
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->array;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }
}
