<?php

namespace Fram\Database;

/**
 * Represents a SQL query to build.
 */
class Query implements \IteratorAggregate
{
    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @var array
     */
    private $select = [];

    /**
     * @var array
     */
    private $from;

    /**
     * @var array
     */
    private $joins = [];

    /**
     * @var array
     */
    private $where = [];

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $order = [];

    /**
     * @var string
     */
    private $limit;

    /**
     * @var string
     */
    private $entity = \stdClass::class;

    /**
     * Constructor.
     *
     * @param \PDO|null $pdo Instance of PDO.
     */
    public function __construct(\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * Adds fields to select.
     *
     * @param string ...$fields
     * @return Query
     */
    public function select(string ...$fields): self
    {
        $this->select = array_merge($this->select, $fields);

        return $this;
    }

    /**
     * Adds table to from.
     *
     * @param string $table
     * @param string|null $alias
     * @return Query
     */
    public function from(string $table, string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }

        return $this;
    }

    /**
     * Adds join.
     *
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return Query
     */
    public function join(string $table, string $condition, string $type = 'LEFT'): self
    {
        $this->joins[$table] = [$type, $condition];

        return $this;
    }

    /**
     * Adds conditions to where. If used, parameters must be specified with
     * the 'params' method.
     *
     * The conditions should be written as ':param' and a corresponding key 'param'
     * must exist in the params used in the 'params' method.
     *
     * @param string ...$conditions
     * @return Query
     */
    public function where(string ...$conditions): self
    {
        $this->where = array_merge($this->where, $conditions);

        return $this;
    }

    /**
     * Defines the params used in the query.
     *
     * @param array $params Key/value pairs.
     * @return Query
     */
    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Adds order.
     *
     * @param string $field
     * @param string $direction
     * @return Query
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->order[] = [$field, $direction];

        return $this;
    }

    /**
     * Specifies the limit.
     *
     * @param int $length
     * @param int $offset
     * @return Query
     */
    public function limit(int $length, int $offset = 0): self
    {
        $this->limit = "LIMIT $length OFFSET $offset";

        return $this;
    }

    /**
     * Specifies which entity will be hydrated with the result.
     *
     * @param string $entity
     * @return Query
     */
    public function into(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function __toString()
    {
        // SELECT
        $parts = ['SELECT'];
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = '*';
        }

        // FROM
        $parts[] = 'FROM';
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key $value";
            } else {
                $from[] = $value;
            }
        }
        $parts[] = join(', ', $from);

        // JOIN
        if (!empty($this->joins)) {
            foreach ($this->joins as $table => $joins) {
                $type = $joins[0];
                $condition = $joins[1];
                $parts[] = strtoupper($type) . " JOIN $table ON $condition";
            }
        }

        // WHERE
        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = '(' . join(') AND (', $this->where) . ')';
        }

        // ORDER BY
        if (!empty($this->order)) {
            $parts[] = 'ORDER BY';
            $order = [];
            foreach ($this->order as $orders) {
                $order[] = "$orders[0] $orders[1]";
            }
            $parts[] = join(', ', $order);
        }

        // LIMIT
        if ($this->limit) {
            $parts[] = $this->limit;
        }
        
        return join(' ', $parts);
    }

    /**
     * Returns the number of rows in the result of the current query.
     *
     * @return int
     */
    public function count(): int
    {
        $query = clone $this;
        $table = reset($this->from);
        $query->select = [];
        return $query->select("COUNT($table.id)")->execute()->fetchColumn();
    }

    /**
     * Executes the request and fetches the result.
     *
     * @return mixed The hydrated object.
     * @throws NoRecordException
     */
    public function fetch()
    {
        $record = $this->execute()->fetch(\PDO::FETCH_ASSOC);
        if ($record === false) {
            throw new NoRecordException();
        }

        return Hydrator::hydrate($record, $this->entity);
    }

    /**
     * Executes the query and returns a QueryResult containing the results.
     *
     * @return QueryResult
     */
    public function fetchAll(): QueryResult
    {
        return new QueryResult(
            $this->execute()->fetchAll(\PDO::FETCH_ASSOC),
            $this->entity
        );
    }

    /**
     * Executes the query.
     *
     * @return PDOStatement|false
     */
    private function execute()
    {
        $query = $this->__toString();
        if ($this->params) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        return $this->pdo->query($query);
    }

    /**
     * Returns the iterator.
     *
     * @return QueryResult
     */
    public function getIterator(): QueryResult
    {
        return $this->fetchAll();
    }
}
