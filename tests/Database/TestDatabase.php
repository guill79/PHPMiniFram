<?php

namespace Tests\Database;

use PHPUnit\Framework\TestCase;
use PDO;

class TestDatabase extends TestCase
{
    protected $pdo;

    public function setUp()
    {
        $this->pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=unit_tests',
            'root',
            '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );

        $this->pdo->query('DROP TABLE IF EXISTS test');
        $this->pdo->query('CREATE TABLE test (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255),
            toto INT
        )');

        $this->pdo->query("
            INSERT INTO test (name, toto) VALUES
            ('bonjour', 3),
            ('salut', 5),
            ('potiche', 6),
            ('patabia', 7)
        ");
    }
}
