<?php

namespace Tests\Database;

use Fram\Database\NoRecordException;
use PDO;
use stdClass;

class ManagerTest extends TestDatabase
{
    protected $pdo;
    private $manager;
    
    public function setUp()
    {
        parent::setUp();
        $this->manager = new SubManager($this->pdo);
    }

    public function testFindAll()
    {
        $results = $this->manager->findAll()->fetchAll();
        $this->assertCount(4, $results);

        $entity0 = new stdClass();
        $entity0->id = 1;
        $entity0->name = 'bonjour';
        $entity0->toto = 3;

        $entity1 = new stdClass();
        $entity1->id = 2;
        $entity1->name = 'salut';
        $entity1->toto = 5;

        $entity2 = new stdClass();
        $entity2->id = 3;
        $entity2->name = 'potiche';
        $entity2->toto = 6;

        $entity3 = new stdClass();
        $entity3->id = 4;
        $entity3->name = 'patabia';
        $entity3->toto = 7;

        $this->assertEquals($entity0, $results[0]);
        $this->assertEquals($entity1, $results[1]);
        $this->assertEquals($entity2, $results[2]);
        $this->assertEquals($entity3, $results[3]);
    }
    
    public function testFind()
    {
        $result = $this->manager->find(1);
        $entity = new stdClass();
        $entity->id = 1;
        $entity->name = 'bonjour';
        $entity->toto = 3;

        $this->assertEquals($entity, $result);

        $result = $this->manager->find(3);
        $entity = new stdClass();
        $entity->id = 3;
        $entity->name = 'potiche';
        $entity->toto = 6;
        $this->assertEquals($entity, $result);
    }

    public function testFindBy()
    {
        $result = $this->manager->findBy('name', 'potiche');
        $entity = new stdClass();
        $entity->id = 3;
        $entity->name = 'potiche';
        $entity->toto = 6;
        $this->assertEquals($entity, $result);

        $result = $this->manager->findBy('toto', 7);
        $entity = new stdClass();
        $entity->id = 4;
        $entity->name = 'patabia';
        $entity->toto = 7;
        $this->assertEquals($entity, $result);
    }

    public function testUpdate()
    {
        $this->assertEquals(1, $this->manager->update(2, [
            'name' => 'updated'
        ]));

        $result = $this->manager->find(2);
        $entity = new stdClass();
        $entity->id = 2;
        $entity->name = 'updated';
        $entity->toto = 5;
        $this->assertEquals($entity, $result);

        $this->assertEquals(1, $this->manager->update(4, [
            'name' => 'sobibor',
            'toto' => 42
        ]));

        $result = $this->manager->find(4);
        $entity = new stdClass();
        $entity->id = 4;
        $entity->name = 'sobibor';
        $entity->toto = 42;
        $this->assertEquals($entity, $result);
    }

    public function testDelete()
    {
        $result = $this->manager->find(1);
        $entity = new stdClass();
        $entity->id = 1;
        $entity->name = 'bonjour';
        $entity->toto = 3;
        $this->assertEquals($entity, $result);

        $this->manager->delete(['id' => 3]);

        $results = $this->manager->findAll()->fetchAll();
        $entity0 = new stdClass();
        $entity0->id = 1;
        $entity0->name = 'bonjour';
        $entity0->toto = 3;

        $entity1 = new stdClass();
        $entity1->id = 2;
        $entity1->name = 'salut';
        $entity1->toto = 5;

        $entity2 = new stdClass();
        $entity2->id = 4;
        $entity2->name = 'patabia';
        $entity2->toto = 7;

        $this->assertEquals($entity0, $results[0]);
        $this->assertEquals($entity1, $results[1]);
        $this->assertEquals($entity2, $results[2]);

        $this->expectException(NoRecordException::class);
        $this->manager->find(3);
    }

    public function testDeleteVariant()
    {
        $this->manager->delete(['name' => 'salut']);
        $this->expectException(NoRecordException::class);
        $this->manager->find(2);
    }

    public function testInsert()
    {
        $this->assertEquals(1, $this->manager->insert([
            'name' => 'patacha',
            'toto' => 5
        ]));

        $result = $this->manager->find(5);
        $entity = new stdClass();
        $entity->id = 5;
        $entity->name = 'patacha';
        $entity->toto = 5;
        $this->assertEquals($entity, $result);

        $this->assertEquals(1, $this->manager->insert([
            'name' => 'titi',
            'toto' => 10
        ]));

        $result = $this->manager->find(6);
        $entity = new stdClass();
        $entity->id = 6;
        $entity->name = 'titi';
        $entity->toto = 10;
        $this->assertEquals($entity, $result);

        $results = $this->manager->findAll()->fetchAll();
        $this->assertCount(6, $results);
    }

    public function testCustomEntity()
    {
        $entityManager = new EntityManager($this->pdo);
        $result = $entityManager->find(3);

        $entity = new Entity();
        $entity->id = 3;
        $entity->name = 'potiche';
        $entity->toto = 6;

        $this->assertEquals($entity, $result);
    }

    public function testCount()
    {
        $count = $this->manager->count();
        $this->assertEquals(4, $count);
    }

    public function testExists()
    {
        $this->assertTrue($this->manager->exists(['name' => 'patabia']));
        $this->assertTrue($this->manager->exists(['id' => 2]));
        $this->assertTrue($this->manager->exists(['toto'=> 5]));
        $this->assertTrue($this->manager->exists(['name' => 'salut', 'toto'=> 5]));
        $this->assertFalse($this->manager->exists(['name' => 'bonjour', 'toto'=> 6]));
        $this->assertFalse($this->manager->exists(['name' => 'jfeisjij']));
        $this->assertFalse($this->manager->exists(['toto' => 34]));
        $this->assertFalse($this->manager->exists(['id' => 7]));
    }
}
