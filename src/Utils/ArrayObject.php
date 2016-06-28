<?php

namespace PhpSchool\PhpWorkshop\Utils;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Utility collection class.
 *
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
     * Accepts an array of items.
     *
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->array = $array;
    }

    /**
     * Run a callable function over each item in the array using the result
     * to construct a new instance of `ArrayObject`.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback)
    {
        return new static (array_map($callback, $this->array));
    }

    /**
     * Implode each item together using the provided glue.
     *
     * @param string $glue
     * @return string
     */
    public function implode($glue)
    {
        return implode($glue, $this->array);
    }

    /**
     * Add a new item on to the beginning of the collection. A new instance is returned.
     *
     * @param mixed $value
     * @return static
     */
    public function prepend($value)
    {
        return new static(array_merge([$value], $this->array));
    }

    /**
     * Add a new item to the end of the collection. A new instance is returned.
     *
     * @param mixed $value
     * @return static
     */
    public function append($value)
    {
        return new static(array_merge($this->array, [$value]));
    }

    /**
     * Return an iterator containing all the items. Allows to `foreach` over.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->array);
    }

    /**
     * Get all the items in the array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return $this->array;
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->array);
    }
}
