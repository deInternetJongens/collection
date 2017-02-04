<?php

namespace OneMustCode\Collection;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use JsonSerializable;
use Closure;

class Collection implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    /** @var array */
    protected $items = [];

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Adds new item to the collection
     *
     * @param mixed $value
     */
    public function add($value)
    {
        $this->items[] = $value;
    }

    /**
     * Returns all items
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Checks if the given value exist in the collection
     *
     * @param mixed $value
     * @return bool
     */
    public function exists($value)
    {
        return in_array($value, $this->items, true);
    }

    /**
     * Returns first from array or given callback
     *
     * @param null $callback
     * @param null $default
     * @return mixed|null
     */
    public function first($callback = null, $default = null)
    {
        if ($this->count() == 0) {
            return $default;
        }

        if ($callback === null) {
            return reset($this->items);
        }

        return $this->filter($callback)->first();
    }

    /**
     * Returns last from array or given callback
     *
     * @param Closure|null $callback
     * @param null $default
     * @return mixed|null
     */
    public function last(Closure $callback = null, $default = null)
    {
        if ($this->count() == 0) {
            return $default;
        }

        if ($callback === null) {
            return end($this->items);
        }

        return $this->filter($callback)->last();
    }

    /**
     * Remove items by the given keys(s)
     *
     * @param string|array $keys
     */
    public function remove($keys)
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }
    }

    /**
     * Get item by the given key
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Returns an new collection with filtered items
     *
     * @param $callback
     * @return Collection
     */
    public function filter($callback)
    {
        return new static(array_filter($this->items, $callback));
    }

    /**
     * Puts mew item to the collection
     *
     * @param $key
     * @param $value
     */
    public function put($key, $value)
    {
        $this->items[$key] = $value;
    }

    /**
     * Merge current collection with given items
     *
     * @param $items
     * @return static
     */
    public function merge($items)
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Retrieve the last item from the collection and removes it
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }


    /**
     * Retrieve the first item from the collection and removes it
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->items);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof ArrayAccess ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Convert the collection to an json string
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } else {
                return $value;
            }
        }, $this->items);
    }

    /**
     * Convert the collection to an string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Transforms given items to an array
     *
     * @param $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof ArrayAccess) {
            return $items->toArray();
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        return (array) $items;
    }
}