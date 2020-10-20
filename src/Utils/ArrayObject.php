<?php

namespace PhpSchool\PhpWorkshop\Utils;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * Utility collection class.
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
    public function __construct(array $array = [])
    {
        $this->array = $array;
    }

    /**
     * Run a callable function over each item in the array using the result
     * to construct a new instance of `ArrayObject`.
     *
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback)
    {
        return new self(array_map($callback, $this->array));
    }

    /**
     * Run a callable over each item in the array and flatten the results by one level returning a new instance of
     * `ArrayObject` with the flattened items.
     *
     * @param callable $callback
     * @return self
     */
    public function flatMap(callable $callback)
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Collapse an array of arrays into a single array returning a new instance of `ArrayObject`
     * with the collapsed items.
     *
     * @return self
     */
    public function collapse()
    {
        $results = [];

        foreach ($this->array as $item) {
            if (!is_array($item)) {
                continue;
            }

            $results = array_merge($results, $item);
        }

        return new self($results);
    }

    /**
     * Reduce the items to a single value.
     *
     * @param  callable  $callback
     * @param  mixed     $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->array, $callback, $initial);
    }

    /**
     * @return self
     */
    public function keys()
    {
        return new self(array_keys($this->array));
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
     * @return self
     */
    public function prepend($value)
    {
        return new self(array_merge([$value], $this->array));
    }

    /**
     * Add a new item to the end of the collection. A new instance is returned.
     *
     * @param mixed $value
     * @return self
     */
    public function append($value)
    {
        return new self(array_merge($this->array, [$value]));
    }

    /**
     * Get an item at the given key.
     *
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (isset($this->array[$key])) {
            return $this->array[$key];
        }

        return $default;
    }

    /**
     * Set the item at a given offset and return a new instance.
     *
     * @param mixed $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        $items = $this->array;
        $items[$key] = $value;
        return new self($items);
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

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->array === [];
    }
}
