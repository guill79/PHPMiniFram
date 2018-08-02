<?php

namespace Fram\Database;

use Fram\Database\Query;
use Fram\Database\QueryResult;
use PDO;

/**
 * Class managing the database tables.
 */
abstract class Manager
{
    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $entity = \stdClass::class;

    /**
     * Constructor.
     *
     * @param PDO $pdo Instance of PDO.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Returns the table.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Returns the alias.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Returns the entity.
     *
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * Returns a list of the records. [$id => $name, ...]
     *
     * @return array
     */
    public function findList(): array
    {
        $results = $this->pdo->query('SELECT id, name FROM ' . $this->table . ' ORDER BY id')
            ->fetchAll(PDO::FETCH_NUM);
        $list = [];
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }

        return $list;
    }

    /**
     * Creates a new query.
     *
     * @return Query
     */
    protected function newQuery(): Query
    {
        return (new Query($this->pdo))
            ->from($this->table, $this->alias)
            ->into($this->entity);
    }

    /**
     * Finds all the records in the table.
     *
     * @return Query
     */
    public function findAll(): Query
    {
        return $this->newQuery();
    }

    /**
     * Retrieves an element from its id.
     *
     * @param int|null $id
     * @return stdClass|$entity
     *
     * @throws NoRecordException
     */
    public function find(?int $id)
    {
        return $this->findBy('id', $id);
    }

    /**
     * Finds all the records by a specific field.
     *
     * @param string $field
     * @param string $value
     * @return Query
     *
     * @throws NoRecordException
     */
    public function findAllBy(string $field, $value): Query
    {
        $placeholder = str_replace('.', '', $field);

        return $this->findAll()
            ->where("$field = :$placeholder")
            ->params([
                "$placeholder" => $value
            ]);
    }

    /**
     * Finds an item by a specific field.
     *
     * @param string $field
     * @param string $value
     * @return mixed The hydrated item.
     *
     * @throws NoRecordException
     */
    public function findBy(string $field, $value)
    {
        if ($value === null) {
            throw new NoRecordException();
        }
        return $this->newQuery()
            ->where("$field = :field")
            ->params([
                'field' => $value
            ])
            ->fetch();
    }

    /**
     * Returns the number of lines in the table.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->findAll()->count();
    }

    /**
     * Updates an item from its id.
     *
     * @param int $id The id of the item.
     * @param array $params The parameters to update. ['param' => $param, ...]
     * @return int The number of rows affected
     *
     * @throws NoRecordException
     */
    public function update(int $id, array $params): int
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        $query = $this->pdo->prepare('UPDATE ' . $this->table . ' SET ' . $fieldQuery . ' WHERE id = :id');
        if (!$query->execute($params)) {
            throw new \Exception('Execute error.');
        }

        return $query->rowCount();
    }

    /**
     * Inserts an item.
     *
     * @param array $params The parameters of the new item.
     * @return int The number of rows affected.
     *
     * @throws NoRecordException
     */
    public function insert(array $params): int
    {
        $fields = array_keys($params);
        $values = join(', ', array_map(function ($field) {
            return ':' . $field;
        }, $fields));
        $req = $this->pdo->prepare(
            'INSERT INTO ' . $this->table . ' (' . join(', ', $fields) . ') '
            . 'VALUES (' . $values . ')'
        );
        if (!$req->execute($params)) {
            throw new \Exception('Execute error.');
        }

        return $req->rowCount();
    }

    /**
     * Deletes an item where the field matches the value.
     *
     * @param array $params The parameters. [$field => $value, ...]
     * @return int The number of rows affected.
     *
     * @throws NoRecordException
     */
    public function delete(array $params): int
    {
        $fields = array_keys($params);
        $values = join(' AND ', array_map(function ($field) {
            return "$field = :$field";
        }, $fields));
        $req = $this->pdo->prepare('DELETE FROM ' . $this->table . ' WHERE ' . $values);
        if (!$req->execute($params)) {
            throw new \Exception('Execute error.');
        }

        return $req->rowCount();
    }

    /**
     * Determines whether an item exists according to the parameters.
     *
     * @param array $params The parameters. [$field => $value]
     * @return bool True if exists, false else.
     */
    public function exists(array $params): bool
    {
        $query = $this->newQuery();
        foreach ($params as $field => $value) {
            $query = $query->where("$field = :$field");
        }
        
        return $query->params($params)->count() !== 0;
    }

    /**
     * Returns the last inserted item ID.
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Begins PDO transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Rollbacks transaction.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Builds field query from the params.
     *
     * @param array $params
     * @return string
     */
    private function buildFieldQuery(array $params): string
    {
        return join(', ', array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($params)));
    }
}
