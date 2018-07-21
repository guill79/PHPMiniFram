<?php

namespace Tests\Database;

use Fram\Database\Query;

class QueryTest extends TestDatabase
{
    public function testSimpleQuery()
    {
        $query = (new Query())
            ->select('toto')
            ->from('tata');
        $this->assertEquals('SELECT toto FROM tata', (string) $query);

        $query = (new Query())
            ->from('grades', 'g');
        $this->assertEquals('SELECT * FROM grades g', (string) $query);
    }

    public function testQueryMultipleSelect()
    {
        $query = (new Query())
            ->select('toto', 'tutu')
            ->select('titi')
            ->from('tata');
        $this->assertEquals('SELECT toto, tutu, titi FROM tata', (string) $query);

        $query = (new Query())
            ->from('grades', 'g');
        $this->assertEquals('SELECT * FROM grades g', (string) $query);
    }

    public function testQueryWhere()
    {
        $query = (new Query())
            ->from('toto', 't')
            ->where('a = :a OR b = :b', 'c = :c');
        $this->assertEquals('SELECT * FROM toto t WHERE (a = :a OR b = :b) AND (c = :c)', (string) $query);

        $query2 = (new Query())
            ->from('posts', 'p')
            ->where('a = :a OR b = :b')
            ->where('c = :c');
        $this->assertEquals('SELECT * FROM posts p WHERE (a = :a OR b = :b) AND (c = :c)', (string) $query2);
    }

    public function testQueryJoin()
    {
        $query = (new Query())
            ->from('grades', 'g')
            ->select('g.grade')
            ->join('users u', 'u.id = g.user_id');
        $this->assertEquals('SELECT g.grade FROM grades g LEFT JOIN users u ON u.id = g.user_id', (string) $query);

        $query = (new Query())
            ->from('grades', 'g')
            ->select('g.grade')
            ->join('users u', 'u.id = g.user_id')
            ->join('toto', 't.id = g.user_id', 'RIGHT');
        $this->assertEquals('SELECT g.grade FROM grades g LEFT JOIN users u ON u.id = g.user_id RIGHT JOIN toto ON t.id = g.user_id', (string) $query);
    }

    public function testOrderBy()
    {
        $query = (new Query())
            ->select('username', 'firstname')
            ->from('users')
            ->orderBy('id', 'DESC');
        $this->assertEquals('SELECT username, firstname FROM users ORDER BY id DESC', (string) $query);

        $query = (new Query())
            ->select('username', 'firstname')
            ->from('users')
            ->orderBy('id', 'DESC')
            ->orderBy('username');
        $this->assertEquals('SELECT username, firstname FROM users ORDER BY id DESC, username ASC', (string) $query);
    }

    public function testQueryLimit()
    {
        $query = (new Query())
            ->from('table')
            ->select('note')
            ->limit(5, 10);
        $this->assertEquals('SELECT note FROM table LIMIT 5 OFFSET 10', (string) $query);

        $query = (new Query())
            ->from('table')
            ->select('note')
            ->limit(5);
        $this->assertEquals('SELECT note FROM table LIMIT 5 OFFSET 0', (string) $query);
    }

    public function testQueryOrderWhere()
    {
        $query = (new Query($this->pdo))
            ->from('grades')
            ->where('id < :id')
            ->orderBy('user_id', 'DESC')
            ->orderBy('grade');
        $this->assertEquals('SELECT * FROM grades WHERE (id < :id) ORDER BY user_id DESC, grade ASC', (string) $query);
    }

    public function testQueryMultipleOrders()
    {
        $query = (new Query())
            ->select('toto')
            ->from('tata')
            ->orderBy('toto', 'DESC')
            ->orderBy('toto', 'IS NULL');
        $this->assertEquals('SELECT toto FROM tata ORDER BY toto DESC, toto IS NULL', (string) $query);

        $query = (new Query())
            ->from('grades', 'g');
        $this->assertEquals('SELECT * FROM grades g', (string) $query);
    }

    public function testQueryJoinOrderLimit()
    {
        $query = (new Query($this->pdo))
            ->from('users', 'u')
            ->select('username')
            ->orderBy('u.id', 'DESC')
            ->orderBy('t.toto')
            ->join('grades g', 'g.user_id = u.id')
            ->limit(5, 10)
            ->join('toto t', 't.id_toto = u.id', 'INNER');
        $this->assertEquals('SELECT username FROM users u LEFT JOIN grades g ON g.user_id = u.id INNER JOIN toto t ON t.id_toto = u.id ORDER BY u.id DESC, t.toto ASC LIMIT 5 OFFSET 10', (string) $query);
    }

    public function testQueryMultipleJoinReplaced()
    {
        $query = (new Query())
            ->from('tutu')
            ->join('tata t', 't.id = titi.id')
            ->join('salut s', 's.id = soso.id')
            ->join('tata t', 't.id_replaced = titi.id', 'inner');
        $this->assertEquals('SELECT * FROM tutu INNER JOIN tata t ON t.id_replaced = titi.id LEFT JOIN salut s ON s.id = soso.id', (string) $query);
    }

    public function testCount()
    {
        $nbItems = (new Query($this->pdo))
            ->select('toto t', 'fjeijf', 'jfiese')
            ->from('test', 't')
            ->count();
        $this->assertEquals(4, $nbItems);

        $nbItems = (new Query($this->pdo))
            ->from('test', 't')
            ->where('t.toto < :number')
            ->params([
                'number' => 6
            ])
            ->count();

        $this->assertEquals(2, $nbItems);
    }

    public function testMultipleParams()
    {
        $nbItems = (new Query($this->pdo))
            ->from('test', 't')
            ->where('t.toto < :number')
            ->where('t.id < :id')
            ->params([
                'id' => 3
            ])
            ->params([
                'number' => 4
            ])
            ->count();

        $this->assertEquals(1, $nbItems);
    }

    public function testHydrateEntity()
    {
        $items = (new Query($this->pdo))
            ->from('test', 't')
            ->into(Entity::class)
            ->fetchAll();
        $this->assertEquals('jour', substr($items[0]->name, -4));

        $actualItem = (new Query($this->pdo))
            ->from('test')
            ->into(Entity::class)
            ->where('toto = :toto')
            ->params([
                'toto' => 6
            ])
            ->fetch();
        $expectedItem = new Entity();
        $expectedItem->id = 3;
        $expectedItem->name = 'potiche';
        $expectedItem->toto = 6;
        $this->assertEquals($expectedItem, $actualItem);
    }

    public function testLazyHydrate()
    {
        $items = (new Query($this->pdo))
            ->from('test', 't')
            ->into(Entity::class)
            ->fetchAll();
        $item = $items[0];
        $item2 = $items[0];
        $this->assertSame($item, $item2);
    }

    public function testFetchStdClass()
    {
        $actualUser = (new Query($this->pdo))
            ->from('test')
            ->where('id = :id')
            ->params([
                'id' => 1
            ])
            ->fetch();

        $expectedUser = new \stdClass();
        $expectedUser->id = 1;
        $expectedUser->name = 'bonjour';
        $expectedUser->toto = 3;
        $this->assertEquals($expectedUser, $actualUser);
    }
}
