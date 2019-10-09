<?php


namespace App\TestService\Entities;


class ListEntity implements \ArrayAccess, \Countable, \Iterator
{
    private $position = 0;
    private $container = [];
    private $counted;
    private $sortedByCount = [];

    public function __construct($data = []) {
        $this->container = $data;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function count()
    {
        return count($this->container);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->container[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->container[$this->position]);
    }

    public function getCounted(): array
    {
        if ($this->counted === null) {
            $this->counted = array_count_values($this->container);
        }
        return $this->counted;
    }

    public function getSortedByCount(int $sortBy = SORT_ASC, int $flag = SORT_REGULAR): array
    {
        if (isset($this->sortedCount[$sortBy])) {
            return $this->sortedByCount[$sortBy];
        }
        $sorted = $this->getCounted();
        asort($sorted, $flag);
        return $this->sortedByCount[$sortBy] = $sortBy === SORT_ASC ? $sorted : array_reverse($sorted);
    }

    public function mostCommon(int $count = 0, int $flag = SORT_REGULAR): array
    {
        $counted = $this->getSortedByCount(SORT_DESC, $flag);
        if ($count > 0) {
            $counted = array_slice($counted, 0, $count);
        }
        $data = [];
        foreach ($counted as $index => $value) {
            $data[] = [$index, $value];
        }
        return $data;
    }

    public function toArray()
    {
        return $this->container;
    }

    public function delete($elem): ListEntity
    {
        if (($index = $this->getIndex($elem)) !== null) {
            unset($this[$index]);
        }
        return $this;
    }

    public function getIndex($elem)
    {
        $index = array_search($elem, $this->container);
        return $index !== false ? $index : null;
    }
}