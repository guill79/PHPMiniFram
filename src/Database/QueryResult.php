<?php

namespace Fram\Database;

/**
 * Represents the result of a query.
 */
class QueryResult implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * @var array
     */
    private $records;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var array
     */
    private $hydratedRecords = [];

    /**
     * Constructor.
     *
     * @param array $records The results of the query (from PDO::fetchAll(PDO::FETCH_ASSOC)).
     * @param string $entity The entity to hydrate with the results.
     */
    public function __construct(array $records, string $entity)
    {
        $this->records = $records;
        $this->entity = $entity;
    }

    /**
     * Returns the hydrated objects as an array.
     *
     * @return $entity[]
     */
    public function getHydratedRecords(): array
    {
        // Hydrate the records
        foreach ($this as $record) {
        }

        return $this->hydratedRecords;
    }

    private function get(int $index)
    {
        if (!isset($this->hydratedRecords[$index])) {
            $this->hydratedRecords[$index] = Hydrator::hydrate($this->records[$index], $this->entity);
        }
        return $this->hydratedRecords[$index];
    }

    /**
     * Returns the current element.
     *
     * @return mixed
     */
    public function current()
    {
        return $this->get($this->index);
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next()
    {
        $this->index++;
    }

    public function key()
    {
        return $this->index;
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->records[$this->index]);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind()
    {
        $this->index = 0;
    }

    /**
     * Whether a offset exists.
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->records[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        throw new \Exception("Error", 1);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetUnset($offset)
    {
        throw new \Exception("Error", 1);
    }

    public function count()
    {
        return count($this->records);
    }
}
