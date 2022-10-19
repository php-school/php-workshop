<?php

declare(strict_types=1);

namespace PhpSchool\PhpWorkshop\Utils;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 *
 * Utility collection class.
 *
 * @template TKey of array-key
 * @template T
 * @implements IteratorAggregate<TKey, T>
 */
class ArrayObject implements IteratorAggregate, Countable
{
    /**
     * @var array<TKey, T>
     */
    private $array;

    /**
     * Accepts an array of items.
     *
     * @param array<TKey, T> $array
     */
    final public function __construct(array $array = [])
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
    public function map(callable $callback): self
    {
        return new static(array_map($callback, $this->array));
    }

    /**
     * Run a callable function over each item in the array
     *
     * @param callable $callback
     * @return static
     */
    public function each(callable $callback): self
    {
        array_walk($this->array, $callback);

        return new static($this->array);
    }

    /**
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): self
    {
        return new static(array_filter($this->array, $callback));
    }

    /**
     * Run a callable over each item in the array and flatten the results by one level returning a new instance of
     * `ArrayObject` with the flattened items.
     *
     * @template TFlatMapReturn of array
     *
     * @param  callable(T): TFlatMapReturn $callback
     * @return static<int|string, mixed>
     */
    public function flatMap(callable $callback): self
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Collapse an array of arrays into a single array returning a new instance of `ArrayObject`
     * with the collapsed items.
     *
     * @return static
     */
    public function collapse(): self
    {
        $results = [];

        foreach ($this->array as $item) {
            if (!is_array($item)) {
                continue;
            }

            $results = array_merge($results, $item);
        }

        return new static($results);
    }

    /**
     * @return static
     */
    public function ksort(): self
    {
        $data = $this->getArrayCopy();
        ksort($data);
        return new static($data);
    }

    /**
     * Reduce the items to a single value.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param  callable(TReduceInitial|TReduceReturnType, T): TReduceReturnType  $callback
     * @param  TReduceInitial $initial
     * @return TReduceInitial|TReduceReturnType
     */
    public function reduce(callable $callback, $initial = null)
    {
        $result = $initial;

        foreach ($this->array as $value) {
            $result = $callback($result, $value);
        }

        return $result;
    }

    /**
     * @return static<int, TKey>
     */
    public function keys(): self
    {
        return new static(array_keys($this->array));
    }

    /**
     * @return static
     */
    public function values(): self
    {
        return new static(array_values($this->array));
    }

    /**
     * @return T|mixed
     */
    public function first()
    {
        return $this->get(0);
    }

    /**
     * Implode each item together using the provided glue.
     *
     * @param string $glue
     * @return string
     */
    public function implode(string $glue): string
    {
        return implode($glue, $this->array);
    }

    /**
     * Add a new item on to the beginning of the collection. A new instance is returned.
     *
     * @param T $value
     * @return static
     */
    public function prepend($value): self
    {
        return new static(array_merge([$value], $this->array));
    }

    /**
     * Add a new item to the end of the collection. A new instance is returned.
     *
     * @param T $value
     * @return static
     */
    public function append($value): self
    {
        return new static(array_merge($this->array, [$value]));
    }

    /**
     * Get an item at the given key.
     *
     * @template TGetDefault
     *
     * @param string|int $key
     * @param TGetDefault $default
     * @return T|TGetDefault
     */
    public function get($key, $default = null)
    {
        return $this->array[$key] ?? $default;
    }

    /**
     * Set the item at a given offset and return a new instance.
     *
     * @param string $key
     * @param T $value
     * @return static
     */
    public function set(string $key, $value): self
    {
        $items = $this->array;
        $items[$key] = $value;
        return new static($items);
    }

    /**
     * Return an iterator containing all the items. Allows to `foreach` over.
     *
     * @return ArrayIterator<TKey, T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->array);
    }

    /**
     * Get all the items in the array.
     *
     * @return array<TKey, T>
     */
    public function getArrayCopy(): array
    {
        return $this->array;
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->array);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->array === [];
    }
}
