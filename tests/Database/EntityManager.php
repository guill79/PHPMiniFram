<?php

namespace Tests\Database;

use Fram\Database\Manager;

class EntityManager extends Manager
{
    protected $table = 'test';

    protected $entity = Entity::class;
}
